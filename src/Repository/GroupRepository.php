<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Repository;

use Cycle\ORM\Select\QueryBuilder;
use Cycle\ORM\Select\Repository;
use Mailery\Brand\Entity\Brand;
use Mailery\Subscriber\Entity\Group;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;
use Mailery\Subscriber\Filter\GroupFilter;
use Yiisoft\Data\Paginator\PaginatorInterface;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\DataReaderInterface;

class GroupRepository extends Repository
{
    /**
     * @param array $scope
     * @param array $orderBy
     * @return DataReaderInterface
     */
    public function getDataReader(array $scope = [], array $orderBy = []): DataReaderInterface
    {
        return new EntityReader($this->select()->where($scope)->orderBy($orderBy));
    }

    /**
     * @param GroupFilter $filter
     * @return PaginatorInterface
     */
    public function getFullPaginator(GroupFilter $filter): PaginatorInterface
    {
        $dataReader = $this->getDataReader();

        if (!$filter->isEmpty()) {
            $dataReader = $dataReader->withFilter($filter);
        }

        return new OffsetPaginator(
            $dataReader->withSort(
                (new Sort([]))->withOrder(['id' => 'DESC'])
            )
        );
    }

    /**
     * @param Brand $brand
     * @return self
     */
    public function withBrand(Brand $brand): self
    {
        $repo = clone $this;
        $repo->select
            ->andWhere([
                'brand_id' => $brand->getId(),
            ]);

        return $repo;
    }

    /**
     * @param string $name
     * @param Group|null $exclude
     * @return Group|null
     */
    public function findByName(string $name, ?Group $exclude = null): ?Group
    {
        return $this
            ->select()
            ->where(function (QueryBuilder $select) use ($name, $exclude) {
                $select->where('name', $name);

                if ($exclude !== null) {
                    $select->where('id', '<>', $exclude->getId());
                }
            })
            ->fetchOne();
    }
}
