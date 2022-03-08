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
use Mailery\Subscriber\Service\GroupCrudService;
use Mailery\Subscriber\Service\SubscriberCrudService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Http\Header;
use Yiisoft\Router\UrlGeneratorInterface as UrlGenerator;
use Yiisoft\Yii\View\ViewRenderer;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Mailery\Brand\BrandLocatorInterface;
use Mailery\Subscriber\Filter\GroupFilter;
use Mailery\Subscriber\Filter\SubscriberFilter;
use Mailery\Subscriber\ValueObject\GroupValueObject;
use Mailery\Widget\Search\Form\SearchForm;
use Mailery\Widget\Search\Model\SearchByList;
use Mailery\Subscriber\Search\GroupSearchBy;
use Mailery\Subscriber\Search\SubscriberSearchBy;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Session\Flash\FlashInterface;

class GroupController
{
    public const TAB_ACTIVE = 'active';
    public const TAB_UNCONFIRMED = 'unconfirmed';
    public const TAB_UNSUBSCRIBED = 'unsubscribed';
    public const TAB_BOUNCED = 'bounced';
    public const TAB_COMPLAINT = 'complaint';

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
     * @var SubscriberRepository
     */
    private SubscriberRepository $subscriberRepo;

    /**
     * @var GroupCrudService
     */
    private GroupCrudService $groupCrudService;

    /**
     * @param ViewRenderer $viewRenderer
     * @param ResponseFactory $responseFactory
     * @param UrlGenerator $urlGenerator
     * @param BrandLocatorInterface $brandLocator
     * @param GroupRepository $groupRepo
     * @param SubscriberRepository $subscriberRepo
     * @param GroupCrudService $groupCrudService
     */
    public function __construct(
        ViewRenderer $viewRenderer,
        ResponseFactory $responseFactory,
        UrlGenerator $urlGenerator,
        BrandLocatorInterface $brandLocator,
        GroupRepository $groupRepo,
        SubscriberRepository $subscriberRepo,
        GroupCrudService $groupCrudService
    ) {
        $this->viewRenderer = $viewRenderer
            ->withController($this)
            ->withViewPath(dirname(dirname(__DIR__)) . '/views');

        $this->responseFactory = $responseFactory;
        $this->urlGenerator = $urlGenerator;
        $this->groupRepo = $groupRepo->withBrand($brandLocator->getBrand());
        $this->subscriberRepo = $subscriberRepo->withBrand($brandLocator->getBrand());
        $this->groupCrudService = $groupCrudService->withBrand($brandLocator->getBrand());
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

        $searchForm = (new SearchForm())
            ->withSearchByList(new SearchByList([
                new GroupSearchBy(),
            ]))
            ->withSearchBy($searchBy)
            ->withSearchPhrase($searchPhrase);

        $filter = (new GroupFilter())
            ->withSearchForm($searchForm);

        $paginator = $this->groupRepo->getFullPaginator($filter)
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
            return $this->responseFactory->createResponse(Status::NOT_FOUND);
        }

        $queryParams = $request->getQueryParams();
        $tab = $queryParams['tab'] ?? null;
        $pageNum = (int) ($queryParams['page'] ?? 1);
        $searchBy = $queryParams['searchBy'] ?? null;
        $searchPhrase = $queryParams['search'] ?? null;

        $searchForm = (new SearchForm())
            ->withSearchByList(new SearchByList([
                new SubscriberSearchBy(),
            ]))
            ->withSearchBy($searchBy)
            ->withSearchPhrase($searchPhrase);

        $filter = (new SubscriberFilter())
            ->withGroup($group)
            ->withSearchForm($searchForm);

        if ($tab === self::TAB_ACTIVE) {
            $filter = $filter->withActive();
        } else if ($tab === self::TAB_UNCONFIRMED) {
            $filter = $filter->withUnconfirmed();
        } else if ($tab === self::TAB_UNSUBSCRIBED) {
            $filter = $filter->withUnsubscribed();
        } else if ($tab === self::TAB_BOUNCED) {
            $filter = $filter->withBounced();
        } else if ($tab === self::TAB_COMPLAINT) {
            $filter = $filter->withComplaint();
        }

        $paginator = $this->subscriberRepo
            ->getFullPaginator($filter)
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->viewRenderer->render('view', compact('searchForm', 'tab', 'group', 'paginator', 'subscriberCounter'));
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param GroupForm $form
     * @return Response
     */
    public function create(Request $request, ValidatorInterface $validator, GroupForm $form): Response
    {
        $body = $request->getParsedBody();

        if (($request->getMethod() === Method::POST) && $form->load($body) && $validator->validate($form)->isValid()) {
            $valueObject = GroupValueObject::fromForm($form);
            $group = $this->groupCrudService->create($valueObject);

            return $this->responseFactory
                ->createResponse(Status::FOUND)
                ->withHeader(Header::LOCATION, $this->urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId()]));
        }

        return $this->viewRenderer->render('create', compact('form'));
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param FlashInterface $flash
     * @param GroupForm $form
     * @return Response
     */
    public function edit(Request $request, ValidatorInterface $validator, FlashInterface $flash, GroupForm $form): Response
    {
        $body = $request->getParsedBody();
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->groupRepo->findByPK($groupId)) === null) {
            return $this->responseFactory->createResponse(Status::NOT_FOUND);
        }

        $form = $form->withEntity($group);

        if ($request->getMethod() === Method::POST && $form->load($body) && $validator->validate($form)->isValid()) {
            $valueObject = GroupValueObject::fromForm($form);
            $this->groupCrudService->update($group, $valueObject);

            $flash->add(
                'success',
                [
                    'body' => 'Data have been saved!',
                ],
                true
            );

            return $this->responseFactory
                ->createResponse(Status::FOUND)
                ->withHeader(Header::LOCATION, $this->urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId()]));
        }

        return $this->viewRenderer->render('edit', compact('form', 'group'));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request): Response
    {
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->groupRepo->findByPK($groupId)) === null) {
            return $this->responseFactory->createResponse(Status::NOT_FOUND);
        }

        $this->groupCrudService->delete($group);

        return $this->responseFactory
            ->createResponse(Status::SEE_OTHER)
            ->withHeader(Header::LOCATION, $this->urlGenerator->generate('/subscriber/group/index'));
    }

    /**
     * @param Request $request
     * @param SubscriberCrudService $subscriberCrudService
     * @return Response
     */
    public function deleteSubscriber(Request $request, SubscriberCrudService $subscriberCrudService): Response
    {
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->groupRepo->findByPK($groupId)) === null) {
            return $this->responseFactory->createResponse(Status::NOT_FOUND);
        }

        $subscriberId = $request->getAttribute('subscriberId');
        if (empty($subscriberId) || ($subscriber = $this->subscriberRepo->findByPK($subscriberId)) === null) {
            return $this->responseFactory->createResponse(Status::NOT_FOUND);
        }

        $subscriberCrudService->delete($subscriber, $group);

        return $this->responseFactory
            ->createResponse(Status::SEE_OTHER)
            ->withHeader(Header::LOCATION, $this->urlGenerator->generate('/subscriber/subscriber/index'));
    }
}
