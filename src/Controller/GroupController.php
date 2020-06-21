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
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Form\GroupForm;
use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Subscriber\Search\GroupSearchBy;
use Mailery\Subscriber\Search\SubscriberSearchBy;
use Mailery\Subscriber\Service\GroupService;
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

class GroupController extends WebController
{
    private const PAGINATION_INDEX = 10;

    /**
     * @param Request $request
     * @param SearchForm $searchForm
     * @param SubscriberCounter $subscriberCounter
     * @return Response
     */
    public function index(Request $request, SearchForm $searchForm, SubscriberCounter $subscriberCounter): Response
    {
        $searchForm = $searchForm->withSearchByList(new SearchByList([
            new GroupSearchBy(),
        ]));

        $queryParams = $request->getQueryParams();
        $pageNum = (int) ($queryParams['page'] ?? 1);

        $dataReader = $this->getGroupRepository()
            ->getDataReader()
            ->withSearch((new Search())->withSearchPhrase($searchForm->getSearchPhrase())->withSearchBy($searchForm->getSearchBy()))
            ->withSort((new Sort([]))->withOrderString('name'));

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->render('index', compact('searchForm', 'paginator', 'subscriberCounter'));
    }

    /**
     * @param Request $request
     * @param SearchForm $searchForm
     * @param SubscriberCounter $subscriberCounter
     * @return Response
     */
    public function view(Request $request, SearchForm $searchForm, SubscriberCounter $subscriberCounter): Response
    {
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->getGroupRepository()->findByPK($groupId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $searchForm = $searchForm->withSearchByList(new SearchByList([
            new SubscriberSearchBy(),
        ]));

        $tab = $request->getQueryParams()['tab'] ?? null;
        $pageNum = (int) ($request->getQueryParams()['page'] ?? 1);

        $repo = $this->getSubscriberRepository();

        switch ($tab) {
            case 'active':
                $dataReader = $repo->withActive()->withGroup($group)->getDataReader();

                break;
            case 'unconfirmed':
                $dataReader = $repo->withUnconfirmed()->withGroup($group)->getDataReader();

                break;
            case 'unsubscribed':
                $dataReader = $repo->withUnsubscribed()->withGroup($group)->getDataReader();

                break;
            case 'bounced':
                $dataReader = $repo->withBounced()->withGroup($group)->getDataReader();

                break;
            case 'complaint':
                $dataReader = $repo->withComplaint()->withGroup($group)->getDataReader();

                break;
            default:
                $dataReader = $repo->withGroup($group)->getDataReader();

                break;
        }

        $dataReader = $dataReader
            ->withSearch((new Search())->withSearchPhrase($searchForm->getSearchPhrase())->withSearchBy($searchForm->getSearchBy()))
            ->withSort((new Sort([]))->withOrderString('email'));

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->render('view', compact('searchForm', 'tab', 'group', 'paginator', 'subscriberCounter'));
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
                return $this->redirect($urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId()]));
            }
        }

        return $this->render('create', compact('groupForm', 'submitted'));
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
        if (empty($groupId) || ($group = $this->getGroupRepository()->findByPK($groupId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
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
                return $this->redirect($urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId()]));
            }
        }

        return $this->render('edit', compact('group', 'groupForm', 'submitted'));
    }

    /**
     * @param Request $request
     * @param GroupService $groupService
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function delete(Request $request, GroupService $groupService, UrlGenerator $urlGenerator): Response
    {
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->getGroupRepository()->findByPK($groupId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $groupService->delete($group);

        return $this->redirect($urlGenerator->generate('/subscriber/group/index'));
    }

    /**
     * @param Request $request
     * @param SubscriberService $subscriberService
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function deleteSubscriber(Request $request, SubscriberService $subscriberService, UrlGenerator $urlGenerator): Response
    {
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->getGroupRepository()->findByPK($groupId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $subscriberId = $request->getAttribute('subscriberId');
        if (empty($subscriberId) || ($subscriber = $this->getSubscriberRepository()->findByPK($subscriberId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $subscriberService->delete($subscriber, $group);

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
