<?php
declare(strict_types=1);

namespace Mailery\Subscriber\Controller;

use Mailery\Subscriber\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Cycle\ORM\ORMInterface;
use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Form\GroupForm;
use Yiisoft\Data\Reader\Sort;
use Mailery\Widget\Dataview\Paginator\OffsetPaginator;
use Yiisoft\Router\UrlGeneratorInterface as UrlGenerator;
use Yiisoft\Http\Method;
use Cycle\ORM\Transaction;
use Mailery\Subscriber\Service\SubscriberService;
use Mailery\Subscriber\Service\GroupService;

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

        /** @var GroupRepository $groupRepo */
        $groupRepo = $orm->getRepository(Group::class);

        $dataReader = $groupRepo->findAll()->withSort((new Sort([]))->withOrderString('name'));
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->render('index', compact('paginator'));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function view(Request $request, ORMInterface $orm): Response
    {
        /** @var GroupRepository $groupRepo */
        $groupRepo = $orm->getRepository(Group::class);
        /** @var SubscriberRepository $subscriberRepo */
        $subscriberRepo = $orm->getRepository(Subscriber::class);

        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $groupRepo->findByPK($groupId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $tab = $request->getQueryParams()['tab'] ?? null;
        $pageNum = (int) ($request->getQueryParams()['page'] ?? 1);

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
            ]);

        $submitted = $request->getMethod() === Method::POST;

        if ($submitted) {
            $groupForm->loadFromServerRequest($request);

            if ($groupForm->isValid() && ($group = $groupForm->save()) !== null) {
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
        /** @var GroupRepository $groupRepo */
        $groupRepo = $orm->getRepository(Group::class);

        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $groupRepo->findByPK($groupId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $groupForm
            ->setAttributes([
                'action' => $request->getUri()->getPath(),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ])
            ->withGroup($group);

        $submitted = $request->getMethod() === Method::POST;

        if ($submitted) {
            $groupForm->loadFromServerRequest($request);

            if ($groupForm->isValid() && ($group = $groupForm->save()) !== null) {
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
        /** @var GroupRepository $groupRepo */
        $groupRepo = $orm->getRepository(Group::class);

        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $groupRepo->findByPK($groupId)) === null) {
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
        /** @var GroupRepository $groupRepo */
        $groupRepo = $orm->getRepository(Group::class);

        /** @var SubscriberRepository $subscriberRepo */
        $subscriberRepo = $orm->getRepository(Subscriber::class);

        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $groupRepo->findByPK($groupId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $subscriberId = $request->getAttribute('subscriberId');
        if (empty($subscriberId) || ($subscriber = $subscriberRepo->findByPK($subscriberId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $subscriberService->delete($subscriber, $group);

        return $this->redirect($urlGenerator->generate('/subscriber/subscriber/index'));
    }

}
