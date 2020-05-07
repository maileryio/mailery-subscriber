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
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Form\GroupForm;
use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Subscriber\Service\GroupService;
use Mailery\Subscriber\Service\SubscriberService;
use Mailery\Widget\Dataview\Paginator\OffsetPaginator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Http\Method;
use Yiisoft\Router\UrlGeneratorInterface as UrlGenerator;

class GroupController extends Controller
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

        $dataReader = $this->getGroupRepository($orm)->getDataReader()->withSort((new Sort([]))->withOrderString('name'));
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
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->getGroupRepository($orm)->findByPK($groupId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $tab = $request->getQueryParams()['tab'] ?? null;
        $pageNum = (int) ($request->getQueryParams()['page'] ?? 1);

        $subscriberRepo = $this->getSubscriberRepository($orm);

        switch ($tab) {
            case 'active':
                $dataReader = $subscriberRepo->findActiveByGroup($group);

                break;
            case 'unconfirmed':
                $dataReader = $subscriberRepo->findUnconfirmedByGroup($group);

                break;
            case 'unsubscribed':
                $dataReader = $subscriberRepo->findUnsubscribedByGroup($group);

                break;
            case 'bounced':
                $dataReader = $subscriberRepo->findBouncedByGroup($group);

                break;
            case 'complaint':
                $dataReader = $subscriberRepo->findComplaintByGroup($group);

                break;
            default:
                $dataReader = $subscriberRepo->findAllByGroup($group);

                break;
        }

        $paginator = (new OffsetPaginator($dataReader->withSort((new Sort([]))->withOrderString('email'))))
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->render('view', compact('tab', 'group', 'paginator'));
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
     * @param ORMInterface $orm
     * @param GroupForm $groupForm
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function edit(Request $request, ORMInterface $orm, GroupForm $groupForm, UrlGenerator $urlGenerator): Response
    {
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->getGroupRepository($orm)->findByPK($groupId)) === null) {
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
     * @param ORMInterface $orm
     * @param GroupService $groupService
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function delete(Request $request, ORMInterface $orm, GroupService $groupService, UrlGenerator $urlGenerator): Response
    {
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->getGroupRepository($orm)->findByPK($groupId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $groupService->delete($group);

        return $this->redirect($urlGenerator->generate('/subscriber/group/index'));
    }

    /**
     * @param Request $request
     * @param ORMInterface $orm
     * @param SubscriberService $subscriberService
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function deleteSubscriber(Request $request, ORMInterface $orm, SubscriberService $subscriberService, UrlGenerator $urlGenerator): Response
    {
        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $this->getGroupRepository($orm)->findByPK($groupId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $subscriberId = $request->getAttribute('subscriberId');
        if (empty($subscriberId) || ($subscriber = $this->getSubscriberRepository($orm)->findByPK($subscriberId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $subscriberService->delete($subscriber, $group);

        return $this->redirect($urlGenerator->generate('/subscriber/subscriber/index'));
    }

    /**
     * @param ORMInterface $orm
     * @return GroupRepository
     */
    private function getGroupRepository(ORMInterface $orm): GroupRepository
    {
        return $orm->getRepository(Group::class);
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
