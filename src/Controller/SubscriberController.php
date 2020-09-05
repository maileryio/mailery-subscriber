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
use Mailery\Subscriber\Form\ImportForm;
use Mailery\Subscriber\Form\SubscriberForm;
use Mailery\Subscriber\Queue\ImportJob;
use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Subscriber\Service\SubscriberCrudService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Http\Method;
use Yiisoft\Router\UrlGeneratorInterface as UrlGenerator;
use Mailery\Subscriber\Service\SubscriberService;
use Mailery\Web\ViewRenderer;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Mailery\Brand\Service\BrandLocatorInterface;
use Mailery\Subscriber\Service\GroupService;

class SubscriberController
{
    private const PAGINATION_INDEX = 10;

    /**
     * @var ViewRenderer
     */
    private ViewRenderer $viewRenderer;

    /**
     * @var ResponseFactory
     */
    private ResponseFactory $responseFactory;

    /**
     * @var GroupRepository
     */
    private GroupRepository $groupRepo;

    /**
     * @var GroupService
     */
    private GroupService $groupService;

    /**
     * @var SubscriberRepository
     */
    private SubscriberRepository $subscriberRepo;

    /**
     * @var SubscriberService
     */
    private SubscriberService $subscriberService;

    /**
     * @param ViewRenderer $viewRenderer
     * @param ResponseFactory $responseFactory
     * @param BrandLocatorInterface $brandLocator
     * @param GroupRepository $groupRepo
     * @param GroupService $groupService
     * @param SubscriberRepository $subscriberRepo
     * @param SubscriberService $subscriberService
     */
    public function __construct(
        ViewRenderer $viewRenderer,
        ResponseFactory $responseFactory,
        BrandLocatorInterface $brandLocator,
        GroupRepository $groupRepo,
        GroupService $groupService,
        SubscriberRepository $subscriberRepo,
        SubscriberService $subscriberService
    ) {
        $this->viewRenderer = $viewRenderer
            ->withController($this)
            ->withCsrf();

        $this->responseFactory = $responseFactory;

        $this->groupRepo = $groupRepo
            ->withBrand($brandLocator->getBrand());
        $this->groupService = $groupService;

        $this->subscriberRepo = $subscriberRepo
            ->withBrand($brandLocator->getBrand());
        $this->subscriberService = $subscriberService;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $queryParams = $request->getQueryParams();
        $pageNum = (int) ($queryParams['page'] ?? 1);
        $searchBy = $queryParams['searchBy'] ?? null;
        $searchPhrase = $queryParams['search'] ?? null;

        $searchForm = $this->subscriberService->getSearchForm()
            ->withSearchBy($searchBy)
            ->withSearchPhrase($searchPhrase);

        $paginator = $this->subscriberService->getFullPaginator($searchForm->getSearchBy())
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->viewRenderer->render('index', compact('searchForm', 'paginator'));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function view(Request $request): Response
    {
        $subscriberId = $request->getAttribute('id');
        if (empty($subscriberId) || ($subscriber = $this->subscriberRepo->findByPK($subscriberId)) === null) {
            return $this->responseFactory->createResponse(404);
        }

        return $this->viewRenderer->render('view', compact('subscriber'));
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
            $group = $this->groupRepo->findByPK($groupId);
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
                return $this->responseFactory
                    ->createResponse(302)
                    ->withHeader('Location', $urlGenerator->generate('/subscriber/subscriber/view', ['id' => $subscriber->getId()]));
            }
        }

        return $this->viewRenderer->render('create', compact('subscriberForm', 'submitted', 'group'));
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
            $group = $this->groupRepo->findByPK($groupId);
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

                return $this->responseFactory
                    ->createResponse(302)
                    ->withHeader('Location', $urlGenerator->generate('/subscriber/import/view', ['id' => $import->getId()]));
            }
        }

        return $this->viewRenderer->render('create', compact('importForm', 'submitted', 'group'));
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
        if (empty($subscriberId) || ($subscriber = $this->subscriberRepo->findByPK($subscriberId)) === null) {
            return $this->responseFactory->createResponse(404);
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
                return $this->responseFactory
                    ->createResponse(302)
                    ->withHeader('Location', $urlGenerator->generate('/subscriber/subscriber/view', ['id' => $subscriber->getId()]));
            }
        }

        return $this->viewRenderer->render('edit', compact('subscriber', 'subscriberForm', 'submitted'));
    }

    /**
     * @param Request $request
     * @param SubscriberCrudService $subscriberCrudService
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function delete(Request $request, SubscriberCrudService $subscriberCrudService, UrlGenerator $urlGenerator): Response
    {
        $subscriberId = $request->getAttribute('id');
        if (empty($subscriberId) || ($subscriber = $this->subscriberRepo->findByPK($subscriberId)) === null) {
            return $this->responseFactory->createResponse(404);
        }

        $subscriberCrudService->delete($subscriber);

        return $this->responseFactory
            ->createResponse(302)
            ->withHeader('Location', $urlGenerator->generate('/subscriber/subscriber/index'));
    }
}
