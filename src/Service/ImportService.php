<?php

namespace Mailery\Subscriber\Service;

use Mailery\Widget\Search\Form\SearchForm;
use Mailery\Widget\Search\Model\SearchByList;
use Mailery\Subscriber\Search\ImportSearchBy;
use Yiisoft\Data\Paginator\PaginatorInterface;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Mailery\Subscriber\Repository\ImportRepository;
use Mailery\Brand\Service\BrandLocator;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\Filter\FilterInterface;

class ImportService
{
    /**
     * @var BrandLocator
     */
    private BrandLocator $brandLocator;

    /**
     * @var ImportRepository
     */
    private ImportRepository $importRepo;

    /**
     * @param BrandLocator $brandLocator
     * @param ImportRepository $importRepo
     */
    public function __construct(BrandLocator $brandLocator, ImportRepository $importRepo)
    {
        $this->brandLocator = $brandLocator;
        $this->importRepo = $importRepo;
    }

    /**
     * @return SearchForm
     */
    public function getSearchForm(): SearchForm
    {
        return (new SearchForm())
            ->withSearchByList(new SearchByList([
                new ImportSearchBy(),
            ]));
    }

    /**
     * @param FilterInterface|null $filter
     * @return PaginatorInterface
     */
    public function getFullPaginator(FilterInterface $filter = null): PaginatorInterface
    {
        $dataReader = $this->importRepo
            ->withBrand($this->brandLocator->getBrand())
            ->getDataReader();

        if ($filter !== null) {
            $dataReader = $dataReader->withFilter($filter);
        }

        return new OffsetPaginator(
            $dataReader->withSort(
                (new Sort([]))->withOrder(['id' => 'DESC'])
            )
        );
    }
}