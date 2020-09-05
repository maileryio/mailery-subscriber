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
use Mailery\Subscriber\Entity\Subscriber;
use Yiisoft\Yii\Cycle\DataReader\SelectDataReader;

class SubscriberRepository extends Repository
{
    /**
     * @param array $scope
     * @param array $orderBy
     * @return SelectDataReader
     */
    public function getDataReader(array $scope = [], array $orderBy = []): SelectDataReader
    {
        return new SelectDataReader($this->select()->where($scope)->orderBy($orderBy));
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
            ->andWhere([
                'groups.id' => $group->getId(),
            ]);

        return $repo;
    }

    /**
     * @return self
     */
    public function withActive(): self
    {
        $repo = clone $this;
        $repo->select
            ->andWhere([
                'confirmed' => true,
                'unsubscribed' => false,
                'bounced' => false,
                'complaint' => false,
            ]);

        return $repo;
    }

    /**
     * @return self
     */
    public function withUnconfirmed(): self
    {
        $repo = clone $this;
        $repo->select
            ->andWhere([
                'confirmed' => false,
            ]);

        return $repo;
    }

    /**
     * @return self
     */
    public function withUnsubscribed(): self
    {
        $repo = clone $this;
        $repo->select
            ->andWhere([
                'unsubscribed' => true,
            ]);

        return $repo;
    }

    /**
     * @return self
     */
    public function withBounced(): self
    {
        $repo = clone $this;
        $repo->select
            ->andWhere([
                'bounced' => true,
            ]);

        return $repo;
    }

    /**
     * @return self
     */
    public function withComplaint(): self
    {
        $repo = clone $this;
        $repo->select
            ->andWhere([
                'complaint' => true,
            ]);

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
