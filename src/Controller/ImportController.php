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
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Entity\ImportError;
use Mailery\Subscriber\Repository\ImportErrorRepository;
use Mailery\Subscriber\Repository\ImportRepository;
use Mailery\Subscriber\Search\ImportSearchBy;
use Mailery\Subscriber\WebController;
use Mailery\Widget\Dataview\Paginator\OffsetPaginator;
use Mailery\Widget\Search\Data\Reader\Search;
use Mailery\Widget\Search\Data\Reader\SelectDataReader;
use Mailery\Widget\Search\Form\SearchForm;
use Mailery\Widget\Search\Model\SearchByList;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Reader\Sort;

class ImportController extends WebController
{
    private const PAGINATION_INDEX = 10;

    /**
     * @param Request $request
     * @param SearchForm $searchForm
     * @param ImportCounter $importCounter
     * @return Response
     */
    public function index(Request $request, SearchForm $searchForm, ImportCounter $importCounter): Response
    {
        $searchForm = $searchForm->withSearchByList(new SearchByList([
            new ImportSearchBy(),
        ]));

        $queryParams = $request->getQueryParams();
        $pageNum = (int) ($queryParams['page'] ?? 1);

        $query = $this->getImportRepository()
            ->select()
            ->with('file');

        $dataReader = (new SelectDataReader($query))
            ->withSearch((new Search())->withSearchPhrase($searchForm->getSearchPhrase())->withSearchBy($searchForm->getSearchBy()))
            ->withSort((new Sort([]))->withOrder(['id' => 'desc']));

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->render('index', compact('searchForm', 'paginator', 'importCounter'));
    }

    /**
     * @param Request $request
     * @param StorageService $storageService
     * @param ImportCounter $importCounter
     * @return Response
     */
    public function view(Request $request, StorageService $storageService, ImportCounter $importCounter): Response
    {
        $importId = $request->getAttribute('id');
        $queryParams = $request->getQueryParams();
        $pageNum = (int) ($queryParams['page'] ?? 1);

        if (empty($importId) || ($import = $this->getImportRepository()->findByPK($importId)) === null) {
            return $this->getResponseFactory()->createResponse(404);
        }

        $fileInfo = $storageService->getFileInfo($import->getFile());

        $query = $this->getImportErrorRepository($import)
            ->select();

        $dataReader = (new SelectDataReader($query))
            ->withSort((new Sort([]))->withOrder(['id' => 'desc']));

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        $importCounter = $importCounter->withImport($import);

        return $this->render('view', compact('import', 'paginator', 'fileInfo', 'importCounter'));
    }

    /**
     * @return ImportRepository
     */
    private function getImportRepository(): ImportRepository
    {
        return $this->getOrm()
            ->getRepository(Import::class)
            ->withBrand($this->getBrandLocator()->getBrand());
    }

    /**
     * @param Import $import
     * @return ImportErrorRepository
     */
    private function getImportErrorRepository(Import $import): ImportErrorRepository
    {
        return $this->getOrm()
            ->getRepository(ImportError::class)
            ->withImport($import);
    }
}
