<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Service;

use Mailery\Widget\Search\Form\SearchForm;
use Mailery\Widget\Search\Model\SearchByList;
use Mailery\Subscriber\Search\GroupSearchBy;
use Yiisoft\Data\Paginator\PaginatorInterface;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Brand\Service\BrandLocator;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\Filter\FilterInterface;

class GroupService
{
    /**
     * @var BrandLocator
     */
    private BrandLocator $brandLocator;

    /**
     * @var GroupRepository
     */
    private GroupRepository $groupRepo;

    /**
     * @param BrandLocator $brandLocator
     * @param GroupRepository $groupRepo
     */
    public function __construct(BrandLocator $brandLocator, GroupRepository $groupRepo)
    {
        $this->brandLocator = $brandLocator;
        $this->groupRepo = $groupRepo;
    }

    /**
     * @return SearchForm
     */
    public function getSearchForm(): SearchForm
    {
        return (new SearchForm())
            ->withSearchByList(new SearchByList([
                new GroupSearchBy(),
            ]));
    }

    /**
     * @param FilterInterface|null $filter
     * @return PaginatorInterface
     */
    public function getFullPaginator(FilterInterface $filter = null): PaginatorInterface
    {
        $dataReader = $this->groupRepo
            ->withBrand($this->brandLocator->getBrand())
            ->getDataReader();

        if ($filter !== null) {
            $dataReader = $dataReader->withFilter($filter);
        }

        return new OffsetPaginator(
            $dataReader->withSort(
                (new Sort([]))->withOrderString('name')
            )
        );
    }
}
