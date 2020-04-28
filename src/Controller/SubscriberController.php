<?php

declare(strict_types=1);

namespace Mailery\Subscriber\Controller;

use Mailery\Subscriber\Controller;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Form\SubscriberForm;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Cycle\ORM\ORMInterface;
use Yiisoft\Data\Reader\Sort;
use Mailery\Widget\Dataview\Paginator\OffsetPaginator;
use Yiisoft\Router\UrlGeneratorInterface as UrlGenerator;
use Yiisoft\Http\Method;
use Cycle\ORM\Transaction;
use Mailery\Subscriber\Service\SubscriberService;

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

        /** @var SubscriberRepository $subscriberRepo */
        $subscriberRepo = $orm->getRepository(Subscriber::class);

        $dataReader = $subscriberRepo->findAll()->withSort((new Sort([]))->withOrderString('email'));
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
        /** @var SubscriberRepository $subscriberRepo */
        $subscriberRepo = $orm->getRepository(Subscriber::class);

        $subscriberId = $request->getAttribute('id');
        if (empty($subscriberId) || ($subscriber = $subscriberRepo->findByPK($subscriberId)) === null) {
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
            ]);

        $submitted = $request->getMethod() === Method::POST;

        if ($submitted) {
            $subscriberForm->loadFromServerRequest($request);

            if ($subscriberForm->isValid() && ($group = $subscriberForm->save()) !== null) {
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
        /** @var SubscriberRepository $subscriberRepo */
        $subscriberRepo = $orm->getRepository(Subscriber::class);

        $subscriberId = $request->getAttribute('id');
        if (empty($subscriberId) || ($subscriber = $subscriberRepo->findByPK($subscriberId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $subscriberForm
            ->withSubscriber($subscriber)
            ->setAttributes([
                'action' => $request->getUri()->getPath(),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ]);

        $submitted = $request->getMethod() === Method::POST;

        if ($submitted) {
            $subscriberForm->loadFromServerRequest($request);

            if ($subscriberForm->isValid() && ($subscriber = $subscriberForm->save()) !== null) {
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
        /** @var SubscriberRepository $subscriberRepo */
        $subscriberRepo = $orm->getRepository(Subscriber::class);

        $subscriberId = $request->getAttribute('id');
        if (empty($subscriberId) || ($subscriber = $subscriberRepo->findByPK($subscriberId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $subscriberService->delete($subscriber);

        return $this->redirect($urlGenerator->generate('/subscriber/subscriber/index'));
    }
}
