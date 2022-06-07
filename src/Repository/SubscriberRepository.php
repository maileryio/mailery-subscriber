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
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Entity\Subscriber;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;
use Mailery\Brand\Entity\Brand;
use Mailery\Subscriber\Filter\SubscriberFilter;
use Yiisoft\Data\Paginator\PaginatorInterface;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\DataReaderInterface;

class SubscriberRepository extends Repository
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
     * @param SubscriberFilter $filter
     * @return PaginatorInterface
     */
    public function getFullPaginator(SubscriberFilter $filter): PaginatorInterface
    {
        $dataReader = $this->getDataReader();

        if (!$filter->isEmpty()) {
            $dataReader = $dataReader->withFilter($filter);
        }

        return new OffsetPaginator(
            $dataReader->withSort(
                Sort::only(['id'])->withOrder(['id' => 'desc'])
            )
        );
    }

    /**
     * @param bool $confirmed
     * @return self
     */
    public function asConfirmed(bool $confirmed): self
    {
        $repo = clone $this;
        $repo->select
            ->andWhere([
                'confirmed' => $confirmed,
            ]);

        return $repo;
    }

    /**
     * @param bool $unsubscribed
     * @return self
     */
    public function asUnsubscribed(bool $unsubscribed): self
    {
        $repo = clone $this;
        $repo->select
            ->andWhere([
                'unsubscribed' => $unsubscribed,
            ]);

        return $repo;
    }

    /**
     * @param bool $bounced
     * @return self
     */
    public function asBounced(bool $bounced): self
    {
        $repo = clone $this;
        $repo->select
            ->andWhere([
                'bounced' => $bounced,
            ]);

        return $repo;
    }

    /**
     * @param bool $complaint
     * @return self
     */
    public function asComplaint(bool $complaint): self
    {
        $repo = clone $this;
        $repo->select
            ->andWhere([
                'complaint' => $complaint,
            ]);

        return $repo;
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
     * @param Group $group
     * @return self
     */
    public function withGroup(Group $group): self
    {
        $repo = clone $this;
        $repo->select
            ->with('groups')
            ->where([
                'groups.id' => $group->getId(),
            ]);

        return $repo;
    }

    /**
     * @param Group[] $groups
     * @return self
     */
    public function withGroups(Group ...$groups): self
    {
        $repo = clone $this;
        $repo->select
            ->with('groups')
            ->where(function(\Cycle\ORM\Select\QueryBuilder $select) use($groups) {
                foreach($groups as $group) {
                    $select->orWhere([
                        'groups.id' => $group->getId(),
                    ]);
                }
            });

        return $repo;
    }

    /**
     * @param string $email
     * @param Subscriber|null $exclude
     * @return Subscriber|null
     */
    public function findByEmail(string $email, ?Subscriber $exclude = null): ?Subscriber
    {
        return $this
            ->select()
            ->where(function (QueryBuilder $select) use ($email, $exclude) {
                $select->where('email', $email);

                if ($exclude !== null) {
                    $select->where('id', '<>', $exclude->getId());
                }
            })
            ->fetchOne();
    }
}
