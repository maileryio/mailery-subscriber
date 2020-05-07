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

use Cycle\ORM\ORMInterface;
use Mailery\Subscriber\Controller;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Form\SubscriberForm;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Subscriber\Service\SubscriberService;
use Mailery\Widget\Dataview\Paginator\OffsetPaginator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Http\Method;
use Yiisoft\Router\UrlGeneratorInterface as UrlGenerator;

class SubscriberController extends Controller
{
    private const PAGINATION_INDEX = 10;

    /**
     * @param Request $request
     * @param ORMInterface $orm
     * @return Response
     */
    public function index(Request $request, ORMInterface $orm): Response
    {
        $queryParams = $request->getQueryParams();
        $pageNum = (int) ($queryParams['page'] ?? 1);

        $dataReader = $this->getSubscriberRepository($orm)->getDataReader()->withSort((new Sort([]))->withOrderString('email'));
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->render('index', compact('paginator'));
    }

    /**
     * @param Request $request
     * @param ORMInterface $orm
     * @return Response
     */
    public function view(Request $request, ORMInterface $orm): Response
    {
        $subscriberId = $request->getAttribute('id');
        if (empty($subscriberId) || ($subscriber = $this->getSubscriberRepository($orm)->findByPK($subscriberId)) === null) {
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
        $subscriberForm
            ->setAttributes([
                'action' => $request->getUri()->getPath(),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ])
        ;

        $submitted = $request->getMethod() === Method::POST;

        if ($submitted) {
            $subscriberForm->loadFromServerRequest($request);

            if (($group = $subscriberForm->save()) !== null) {
                return $this->redirect($urlGenerator->generate('/subscriber/subscriber/view', ['id' => $group->getId()]));
            }
        }

        return $this->render('create', compact('subscriberForm', 'submitted'));
    }

    /**
     * @param Request $request
     * @param ORMInterface $orm
     * @param SubscriberForm $subscriberForm
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function edit(Request $request, ORMInterface $orm, SubscriberForm $subscriberForm, UrlGenerator $urlGenerator): Response
    {
        $subscriberId = $request->getAttribute('id');
        if (empty($subscriberId) || ($subscriber = $this->getSubscriberRepository($orm)->findByPK($subscriberId)) === null) {
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
     * @param ORMInterface $orm
     * @param SubscriberService $subscriberService
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function delete(Request $request, ORMInterface $orm, SubscriberService $subscriberService, UrlGenerator $urlGenerator): Response
    {
        $subscriberId = $request->getAttribute('id');
        if (empty($subscriberId) || ($subscriber = $this->getSubscriberRepository($orm)->findByPK($subscriberId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $subscriberService->delete($subscriber);

        return $this->redirect($urlGenerator->generate('/subscriber/subscriber/index'));
    }

    /**
     * @param ORMInterface $orm
     * @return SubscriberRepository
     */
    private function getSubscriberRepository(ORMInterface $orm): SubscriberRepository
    {
        return $orm->getRepository(Subscriber::class);
    }
}
