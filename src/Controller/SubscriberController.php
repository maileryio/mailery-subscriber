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
use Yiisoft\Http\Status;
use Yiisoft\Http\Header;
use Yiisoft\Router\UrlGeneratorInterface as UrlGenerator;
use Yiisoft\Yii\View\ViewRenderer;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Mailery\Brand\BrandLocatorInterface;
use Mailery\Subscriber\Filter\SubscriberFilter;
use Mailery\Widget\Search\Form\SearchForm;
use Mailery\Widget\Search\Model\SearchByList;
use Mailery\Subscriber\Search\SubscriberSearchBy;
use Yiisoft\Validator\ValidatorInterface;
use Mailery\Subscriber\ValueObject\SubscriberValueObject;
use Yiisoft\Session\Flash\FlashInterface;
use Mailery\Subscriber\Service\ImportCrudService;
use Mailery\Subscriber\ValueObject\ImportValueObject;

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
     * @var UrlGenerator
     */
    private UrlGenerator $urlGenerator;

    /**
     * @var GroupRepository
     */
    private GroupRepository $groupRepo;

    /**
     * @var SubscriberRepository
     */
    private SubscriberRepository $subscriberRepo;

    /**
     * @var SubscriberCrudService
     */
    private SubscriberCrudService $subscriberCrudService;

    /**
     * @var ImportCrudService
     */
    private ImportCrudService $importCrudService;

    /**
     * @param ViewRenderer $viewRenderer
     * @param ResponseFactory $responseFactory
     * @param UrlGenerator $urlGenerator
     * @param BrandLocatorInterface $brandLocator
     * @param GroupRepository $groupRepo
     * @param SubscriberRepository $subscriberRepo
     * @param SubscriberCrudService $subscriberCrudService
     * @param ImportCrudService $importCrudService
     */
    public function __construct(
        ViewRenderer $viewRenderer,
        ResponseFactory $responseFactory,
        UrlGenerator $urlGenerator,
        BrandLocatorInterface $brandLocator,
        GroupRepository $groupRepo,
        SubscriberRepository $subscriberRepo,
        SubscriberCrudService $subscriberCrudService,
        ImportCrudService $importCrudService
    ) {
        $this->viewRenderer = $viewRenderer
            ->withController($this)
            ->withViewPath(dirname(dirname(__DIR__)) . '/views');

        $this->responseFactory = $responseFactory;
        $this->urlGenerator = $urlGenerator;
        $this->groupRepo = $groupRepo->withBrand($brandLocator->getBrand());
        $this->subscriberRepo = $subscriberRepo->withBrand($brandLocator->getBrand());
        $this->subscriberCrudService = $subscriberCrudService->withBrand($brandLocator->getBrand());
        $this->importCrudService = $importCrudService->withBrand($brandLocator->getBrand());
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

        $searchForm = (new SearchForm())
            ->withSearchByList(new SearchByList([
                new SubscriberSearchBy(),
            ]))
            ->withSearchBy($searchBy)
            ->withSearchPhrase($searchPhrase);

        $filter = (new SubscriberFilter())
            ->withSearchForm($searchForm);

        $paginator = $this->subscriberRepo->getFullPaginator($filter)
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
            return $this->responseFactory->createResponse(Status::NOT_FOUND);
        }

        return $this->viewRenderer->render('view', compact('subscriber'));
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param SubscriberForm $form
     * @return Response
     */
    public function create(Request $request, ValidatorInterface $validator, SubscriberForm $form): Response
    {
        $body = $request->getParsedBody();
        $groupId = $request->getQueryParams()['groupId'] ?? null;
        $group = $groupId ? $this->groupRepo->findByPK($groupId) : null;

        if (($request->getMethod() === Method::POST) && $form->load($body) && $validator->validate($form)->isValid()) {
            $valueObject = SubscriberValueObject::fromForm($form);
            $subscriber = $this->subscriberCrudService->create($valueObject);

            return $this->responseFactory
                ->createResponse(Status::FOUND)
                ->withHeader(Header::LOCATION, $this->urlGenerator->generate('/subscriber/subscriber/view', ['id' => $subscriber->getId()]));
        }

        return $this->viewRenderer->render('create', compact('form', 'group'));
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param FlashInterface $flash
     * @param SubscriberForm $form
     * @return Response
     */
    public function edit(Request $request, ValidatorInterface $validator, FlashInterface $flash, SubscriberForm $form): Response
    {
        $body = $request->getParsedBody();
        $subscriberId = $request->getAttribute('id');
        if (empty($subscriberId) || ($subscriber = $this->subscriberRepo->findByPK($subscriberId)) === null) {
            return $this->responseFactory->createResponse(Status::NOT_FOUND);
        }

        $form = $form->withEntity($subscriber);

        if ($request->getMethod() === Method::POST && $form->load($body) && $validator->validate($form)->isValid()) {
            $valueObject = SubscriberValueObject::fromForm($form);
            $this->subscriberCrudService->update($subscriber, $valueObject);

            $flash->add(
                'success',
                [
                    'body' => 'Data have been saved!',
                ],
                true
            );

            return $this->responseFactory
                ->createResponse(Status::FOUND)
                ->withHeader(Header::LOCATION, $this->urlGenerator->generate('/subscriber/subscriber/view', ['id' => $subscriber->getId()]));
        }

        return $this->viewRenderer->render('edit', compact('form', 'subscriber'));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request): Response
    {
        $subscriberId = $request->getAttribute('id');
        if (empty($subscriberId) || ($subscriber = $this->subscriberRepo->findByPK($subscriberId)) === null) {
            return $this->responseFactory->createResponse(Status::NOT_FOUND);
        }

        $this->subscriberCrudService->delete($subscriber);

        return $this->responseFactory
            ->createResponse(Status::SEE_OTHER)
            ->withHeader(Header::LOCATION, $this->urlGenerator->generate('/subscriber/subscriber/index'));
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param ImportForm $form
     * @param ImportJob $job
     * @return Response
     */
    public function import(Request $request, ValidatorInterface $validator, ImportForm $form, ImportJob $job): Response
    {
        $body = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $groupId = $request->getQueryParams()['groupId'] ?? null;
        $group = $groupId ? $this->groupRepo->findByPK($groupId) : null;

        if (($request->getMethod() === Method::POST) && $form->load($body) && $form->load($files) && $validator->validate($form)->isValid()) {
            $valueObject = ImportValueObject::fromForm($form);
            $import = $this->importCrudService->create($valueObject);

            $job->push($import);

            return $this->responseFactory
                ->createResponse(Status::FOUND)
                ->withHeader(Header::LOCATION, $this->urlGenerator->generate('/subscriber/import/view', ['id' => $import->getId()]));
        }

        return $this->viewRenderer->render('create', compact('form', 'group'));
    }
}
