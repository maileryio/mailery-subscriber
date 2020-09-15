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
use Mailery\Subscriber\Search\SubscriberSearchBy;
use Yiisoft\Data\Paginator\PaginatorInterface;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Brand\Service\BrandLocator;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\Filter\FilterInterface;

class SubscriberService
{
    /**
     * @var BrandLocator
     */
    private BrandLocator $brandLocator;

    /**
     * @var SubscriberRepository
     */
    private SubscriberRepository $subscriberRepo;

    /**
     * @param BrandLocator $brandLocator
     * @param SubscriberRepository $subscriberRepo
     */
    public function __construct(BrandLocator $brandLocator, SubscriberRepository $subscriberRepo)
    {
        $this->brandLocator = $brandLocator;
        $this->subscriberRepo = $subscriberRepo;
    }

    /**
     * @return SearchForm
     */
    public function getSearchForm(): SearchForm
    {
        return (new SearchForm())
            ->withSearchByList(new SearchByList([
                new SubscriberSearchBy(),
            ]));
    }

    /**
     * @param FilterInterface|null $filter
     * @return PaginatorInterface
     */
    public function getFullPaginator(FilterInterface $filter = null): PaginatorInterface
    {
        $dataReader = $this->subscriberRepo
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
