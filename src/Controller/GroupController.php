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

use Mailery\Subscriber\Counter\SubscriberCounter;
use Mailery\Subscriber\Form\GroupForm;
use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Subscriber\Service\GroupService;
use Mailery\Subscriber\Service\GroupCrudService;
use Mailery\Subscriber\Service\SubscriberCrudService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Http\Method;
use Yiisoft\Router\UrlGeneratorInterface as UrlGenerator;
use Mailery\Subscriber\Service\SubscriberService;
use Mailery\Web\ViewRenderer;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Mailery\Brand\Service\BrandLocatorInterface;

class GroupController
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
     * @param SubscriberCounter $subscriberCounter
     * @return Response
     */
    public function index(Request $request, SubscriberCounter $subscriberCounter): Response
    {
        $queryParams = $request->getQueryParams();
        $pageNum = (int) ($queryParams['page'] ?? 1);
        $searchBy = $queryParams['searchBy'] ?? null;
        $searchPhrase = $queryParams['search'] ?? null;

        $searchForm = $this->groupService->getSearchForm()
            ->withSearchBy($searchBy)
            ->withSearchPhrase($searchPhrase);

        $paginator = $this->groupService->getFullPaginator($searchForm->getSearchBy())
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->viewRenderer->render('index', compact('searchForm', 'paginator', 'subscriberCounter'));
    }

    /**
     * @param Request $request
     * @param SubscriberCounter $subscriberCounter
     * @return Response
     */
    public function view(Request $request, SubscriberCounter $subscriberCounter): Response
    {
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->groupRepo->findByPK($groupId)) === null) {
            return $this->responseFactory->createResponse(404);
        }

        $queryParams = $request->getQueryParams();
        $tab = $queryParams['tab'] ?? null;
        $pageNum = (int) ($queryParams['page'] ?? 1);
        $searchBy = $queryParams['searchBy'] ?? null;
        $searchPhrase = $queryParams['search'] ?? null;

        $searchForm = $this->subscriberService->getSearchForm()
            ->withSearchBy($searchBy)
            ->withSearchPhrase($searchPhrase);

        switch ($tab) {
            case 'active':
                $dataReader = $this->subscriberRepo->withActive()->withGroup($group)->getDataReader();

                break;
            case 'unconfirmed':
                $dataReader = $this->subscriberRepo->withUnconfirmed()->withGroup($group)->getDataReader();

                break;
            case 'unsubscribed':
                $dataReader = $this->subscriberRepo->withUnsubscribed()->withGroup($group)->getDataReader();

                break;
            case 'bounced':
                $dataReader = $this->subscriberRepo->withBounced()->withGroup($group)->getDataReader();

                break;
            case 'complaint':
                $dataReader = $this->subscriberRepo->withComplaint()->withGroup($group)->getDataReader();

                break;
            default:
                $dataReader = $this->subscriberRepo->withGroup($group)->getDataReader();

                break;
        }

        $paginator = $this->subscriberService->getFullPaginator($searchForm->getSearchBy())
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->viewRenderer->render('view', compact('searchForm', 'tab', 'group', 'paginator', 'subscriberCounter'));
    }

    /**
     * @param Request $request
     * @param GroupForm $groupForm
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function create(Request $request, GroupForm $groupForm, UrlGenerator $urlGenerator): Response
    {
        $groupForm
            ->setAttributes([
                'action' => $request->getUri()->getPath(),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ])
        ;

        $submitted = $request->getMethod() === Method::POST;

        if ($submitted) {
            $groupForm->loadFromServerRequest($request);

            if (($group = $groupForm->save()) !== null) {
                return $this->responseFactory
                    ->createResponse(302)
                    ->withHeader('Location', $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId()]));
            }
        }

        return $this->viewRenderer->render('create', compact('groupForm', 'submitted'));
    }

    /**
     * @param Request $request
     * @param GroupForm $groupForm
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function edit(Request $request, GroupForm $groupForm, UrlGenerator $urlGenerator): Response
    {
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->groupRepo->findByPK($groupId)) === null) {
            return $this->responseFactory->createResponse(404);
        }

        $groupForm
            ->withGroup($group)
            ->setAttributes([
                'action' => $request->getUri()->getPath(),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ])
        ;

        $submitted = $request->getMethod() === Method::POST;

        if ($submitted) {
            $groupForm->loadFromServerRequest($request);

            if ($groupForm->save() !== null) {
                return $this->responseFactory
                    ->createResponse(302)
                    ->withHeader('Location', $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId()]));
            }
        }

        return $this->viewRenderer->render('edit', compact('group', 'groupForm', 'submitted'));
    }

    /**
     * @param Request $request
     * @param GroupCrudService $groupCrudService
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function delete(Request $request, GroupCrudService $groupCrudService, UrlGenerator $urlGenerator): Response
    {
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->groupRepo->findByPK($groupId)) === null) {
            return $this->responseFactory->createResponse(404);
        }

        $groupCrudService->delete($group);

        return $this->responseFactory
            ->createResponse(302)
            ->withHeader('Location', $urlGenerator->generate('/subscriber/group/index'));
    }

    /**
     * @param Request $request
     * @param SubscriberCrudService $subscriberCrudService
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function deleteSubscriber(Request $request, SubscriberCrudService $subscriberCrudService, UrlGenerator $urlGenerator): Response
    {
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->groupRepo->findByPK($groupId)) === null) {
            return $this->responseFactory->createResponse(404);
        }

        $subscriberId = $request->getAttribute('subscriberId');
        if (empty($subscriberId) || ($subscriber = $this->subscriberRepo->findByPK($subscriberId)) === null) {
            return $this->responseFactory->createResponse(404);
        }

        $subscriberCrudService->delete($subscriber, $group);

        return $this->responseFactory
            ->createResponse(302)
            ->withHeader('Location', $urlGenerator->generate('/subscriber/subscriber/index'));
    }
}
