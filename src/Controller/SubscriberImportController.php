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

use Mailery\Subscriber\Entity\SubscriberImport;
use Mailery\Subscriber\Repository\SubscriberImportRepository;
use Mailery\Subscriber\Search\SubscriberImportSearchBy;
use Mailery\Subscriber\WebController;
use Mailery\Widget\Dataview\Paginator\OffsetPaginator;
use Mailery\Widget\Search\Data\Reader\Search;
use Mailery\Widget\Search\Data\Reader\SelectDataReader;
use Mailery\Widget\Search\Form\SearchForm;
use Mailery\Widget\Search\Model\SearchByList;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SubscriberImportController extends WebController
{
    private const PAGINATION_INDEX = 10;

    /**
     * @param Request $request
     * @param SearchForm $searchForm
     * @return Response
     */
    public function index(Request $request, SearchForm $searchForm): Response
    {
        $searchForm = $searchForm->withSearchByList(new SearchByList([
            new SubscriberImportSearchBy(),
        ]));

        $queryParams = $request->getQueryParams();
        $pageNum = (int) ($queryParams['page'] ?? 1);

        $query = $this->getSubscriberImportRepository()
            ->select()
            ->with('file');

        $dataReader = (new SelectDataReader($query))
            ->withSearch((new Search())->withSearchPhrase($searchForm->getSearchPhrase())->withSearchBy($searchForm->getSearchBy()));

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->render('index', compact('searchForm', 'paginator'));
    }

    /**
     * @return Response
     */
    public function view(): Response
    {
        return $this->render('index');
    }

    /**
     * @return SubscriberImportRepository
     */
    private function getSubscriberImportRepository(): SubscriberImportRepository
    {
        return $this->getOrm()
            ->getRepository(SubscriberImport::class)
            ->withBrand($this->getBrandLocator()->getBrand());
    }
}
