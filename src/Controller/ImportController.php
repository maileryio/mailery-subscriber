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

use Mailery\Storage\Service\StorageService;
use Mailery\Subscriber\Counter\ImportCounter;
use Mailery\Subscriber\Repository\ImportErrorRepository;
use Mailery\Subscriber\Repository\ImportRepository;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\View\ViewRenderer;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Mailery\Brand\Service\BrandLocatorInterface;
use Mailery\Subscriber\Filter\ImportFilter;
use Mailery\Widget\Search\Form\SearchForm;
use Mailery\Widget\Search\Model\SearchByList;
use Mailery\Subscriber\Search\ImportSearchBy;

class ImportController
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
     * @var ImportRepository
     */
    private ImportRepository $importRepo;

    /**
     * @var ImportErrorRepository
     */
    private ImportErrorRepository $importErrorRepo;

    /**
     * @var StorageService
     */
    private StorageService $storageService;

    /**
     * @param ViewRenderer $viewRenderer
     * @param ResponseFactory $responseFactory
     * @param BrandLocatorInterface $brandLocator
     * @param ImportRepository $importRepo
     * @param ImportErrorRepository $importErrorRepo
     * @param StorageService $storageService
     */
    public function __construct(
        ViewRenderer $viewRenderer,
        ResponseFactory $responseFactory,
        BrandLocatorInterface $brandLocator,
        ImportRepository $importRepo,
        ImportErrorRepository $importErrorRepo,
        StorageService $storageService
    ) {
        $this->viewRenderer = $viewRenderer
            ->withController($this)
            ->withViewBasePath(dirname(dirname(__DIR__)) . '/views');

        $this->responseFactory = $responseFactory;
        $this->importRepo = $importRepo->withBrand($brandLocator->getBrand());
        $this->importErrorRepo = $importErrorRepo;
        $this->storageService = $storageService;
    }

    /**
     * @param Request $request
     * @param ImportCounter $importCounter
     * @return Response
     */
    public function index(Request $request, ImportCounter $importCounter): Response
    {
        $queryParams = $request->getQueryParams();
        $pageNum = (int) ($queryParams['page'] ?? 1);
        $searchBy = $queryParams['searchBy'] ?? null;
        $searchPhrase = $queryParams['search'] ?? null;

        $searchForm = (new SearchForm())
            ->withSearchByList(new SearchByList([
                new ImportSearchBy(),
            ]))
            ->withSearchBy($searchBy)
            ->withSearchPhrase($searchPhrase);

        $filter = (new ImportFilter())
            ->withSearchForm($searchForm);

        $paginator = $this->importRepo->getFullPaginator($filter)
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->viewRenderer->render('index', compact('searchForm', 'paginator', 'importCounter'));
    }

    /**
     * @param Request $request
     * @param ImportCounter $importCounter
     * @return Response
     */
    public function view(Request $request, ImportCounter $importCounter): Response
    {
        $importId = $request->getAttribute('id');
        $queryParams = $request->getQueryParams();
        $pageNum = (int) ($queryParams['page'] ?? 1);

        if (empty($importId) || ($import = $this->importRepo->findByPK($importId)) === null) {
            return $this->responseFactory->createResponse(404);
        }

        $fileInfo = $this->storageService->getFileInfo($import->getFile());

        $query = $this->importErrorRepo
            ->withImport($import)
            ->select();

        $dataReader = (new EntityReader($query))
            ->withSort(Sort::only(['id'])->withOrder(['id' => 'desc']));

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        $importCounter = $importCounter->withImport($import);

        return $this->viewRenderer->render('view', compact('import', 'paginator', 'fileInfo', 'importCounter'));
    }
}
