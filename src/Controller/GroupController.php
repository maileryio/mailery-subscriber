<?php
declare(strict_types=1);

namespace Mailery\Subscriber\Controller;

use Mailery\Subscriber\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Cycle\ORM\ORMInterface;
use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Form\GroupForm;
use Yiisoft\Data\Reader\Sort;
use Mailery\Widget\Dataview\Paginator\OffsetPaginator;
use Yiisoft\Router\UrlGeneratorInterface as UrlGenerator;
use Yiisoft\Http\Method;
use Cycle\ORM\Transaction;

class GroupController extends Controller
{

    private const PAGINATION_INDEX = 5;

    /**
     * @param Request $request
     * @param ORMInterface $orm
     * @return Response
     */
    public function index(Request $request, ORMInterface $orm): Response
    {
        $pageNum = (int) $request->getAttribute('page', 1);
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

        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $groupRepo->findByPK($groupId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        return $this->render('view', compact('group'));
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
     * @param UrlGenerator $urlGenerator
     * @return Response
     */
    public function delete(Request $request, ORMInterface $orm, UrlGenerator $urlGenerator): Response
    {
        /** @var GroupRepository $groupRepo */
        $groupRepo = $orm->getRepository(Group::class);

        $groupId = $request->getAttribute('id');
        if (empty($groupId) || ($group = $groupRepo->findByPK($groupId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $tr = new Transaction($orm);
        $tr->delete($group);
        $tr->run();

        return $this->redirect($urlGenerator->generate('/subscriber/group/index'));
    }

}
