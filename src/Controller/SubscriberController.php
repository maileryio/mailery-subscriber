<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Controller;

use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Form\SubscriberForm;
use Mailery\Subscriber\Form\ImportForm;
use Mailery\Subscriber\Queue\ImportJob;
use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Subscriber\Search\SubscriberSearchBy;
use Mailery\Subscriber\Service\SubscriberService;
use Mailery\Subscriber\WebController;
use Mailery\Widget\Dataview\Paginator\OffsetPaginator;
use Mailery\Widget\Search\Data\Reader\Search;
use Mailery\Widget\Search\Form\SearchForm;
use Mailery\Widget\Search\Model\SearchByList;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Http\Method;
use Yiisoft\Router\UrlGeneratorInterface as UrlGenerator;

class SubscriberController extends WebController
{
    private const PAGINATION_INDEX = 10;

    /**
     * @param Request $request
     * @param SearchForm $searchForm
     * @return Response
     */
    public function index(Request $request, SearchForm $searchForm): Response
    {
        $searchForm = $searchForm->withSearchByList(new SearchByList([
            new SubscriberSearchBy(),
        ]));

        $queryParams = $request->getQueryParams();
        $pageNum = (int) ($queryParams['page'] ?? 1);

        $dataReader = $this->getSubscriberRepository()
            ->getDataReader()
            ->withSearch((new Search())->withSearchPhrase($searchForm->getSearchPhrase())->withSearchBy($searchForm->getSearchBy()))
            ->withSort((new Sort([]))->withOrderString('email'));

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->render('index', compact('searchForm', 'paginator'));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function view(Request $request): Response
    {
        $subscriberId = $request->getAttribute('id');
        if (empty($subscriberId) || ($subscriber = $this->getSubscriberRepository()->findByPK($subscriberId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        return $this->render('view', compact('subscriber'));
    }

    /**
     * @param Request $request
     * @param SubscriberForm $subscriberForm
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function create(Request $request, SubscriberForm $subscriberForm, UrlGenerator $urlGenerator): Response
    {
        $groupId = $request->getQueryParams()['groupId'] ?? null;
        $submitted = $request->getMethod() === Method::POST;

        $group = null;
        if (!empty($groupId)) {
            $group = $this->getGroupRepository()->findByPK($groupId);
        }

        $subscriberForm
            ->setAttributes([
                'action' => $request->getUri()->getPath(),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ])
        ;

        if ($submitted) {
            $subscriberForm->loadFromServerRequest($request);

            if (($subscriber = $subscriberForm->save()) !== null) {
                return $this->redirect($urlGenerator->generate('/subscriber/subscriber/view', ['id' => $subscriber->getId()]));
            }
        }

        return $this->render('create', compact('subscriberForm', 'submitted', 'group'));
    }

    /**
     * @param Request $request
     * @param ImportForm $importForm
     * @param UrlGenerator $urlGenerator
     * @param ImportJob $importJob
     * @return Response
     */
    public function import(Request $request, ImportForm $importForm, UrlGenerator $urlGenerator, ImportJob $importJob): Response
    {
        $groupId = $request->getQueryParams()['groupId'] ?? null;
        $submitted = $request->getMethod() === Method::POST;

        $group = null;
        if (!empty($groupId)) {
            $group = $this->getGroupRepository()->findByPK($groupId);
        }

        $importForm
            ->setAttributes([
                'action' => $request->getUri()->getPath(),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ])
        ;

        if ($submitted) {
            $importForm->loadFromServerRequest($request);

            if (($import = $importForm->import()) !== null) {
                $importJob->push($import);
                return $this->redirect($urlGenerator->generate('/subscriber/import/view', ['id' => $import->getId()]));
            }
        }

        return $this->render('create', compact('importForm', 'submitted', 'group'));
    }

    /**
     * @param Request $request
     * @param SubscriberForm $subscriberForm
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function edit(Request $request, SubscriberForm $subscriberForm, UrlGenerator $urlGenerator): Response
    {
        $subscriberId = $request->getAttribute('id');
        if (empty($subscriberId) || ($subscriber = $this->getSubscriberRepository()->findByPK($subscriberId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $subscriberForm
            ->withSubscriber($subscriber)
            ->setAttributes([
                'action' => $request->getUri()->getPath(),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ])
        ;

        $submitted = $request->getMethod() === Method::POST;

        if ($submitted) {
            $subscriberForm->loadFromServerRequest($request);

            if ($subscriberForm->save() !== null) {
                return $this->redirect($urlGenerator->generate('/subscriber/subscriber/view', ['id' => $subscriber->getId()]));
            }
        }

        return $this->render('edit', compact('subscriber', 'subscriberForm', 'submitted'));
    }

    /**
     * @param Request $request
     * @param SubscriberService $subscriberService
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function delete(Request $request, SubscriberService $subscriberService, UrlGenerator $urlGenerator): Response
    {
        $subscriberId = $request->getAttribute('id');
        if (empty($subscriberId) || ($subscriber = $this->getSubscriberRepository()->findByPK($subscriberId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $subscriberService->delete($subscriber);

        return $this->redirect($urlGenerator->generate('/subscriber/subscriber/index'));
    }

    /**
     * @return GroupRepository
     */
    private function getGroupRepository(): GroupRepository
    {
        return $this->getOrm()
            ->getRepository(Group::class)
            ->withBrand($this->getBrandLocator()->getBrand());
    }

    /**
     * @return SubscriberRepository
     */
    private function getSubscriberRepository(): SubscriberRepository
    {
        return $this->getOrm()
            ->getRepository(Subscriber::class)
            ->withBrand($this->getBrandLocator()->getBrand());
    }
}
