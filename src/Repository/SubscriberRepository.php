<?php

declare(strict_types=1);

namespace Mailery\Subscriber\Repository;

use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Entity\Subscriber;
use Cycle\ORM\Select\Repository;
use Cycle\ORM\Select\QueryBuilder;
use Yiisoft\Yii\Cycle\DataReader\SelectDataReader;

class SubscriberRepository extends Repository
{
    /**
     * @param array $scope
     * @param array $orderBy
     * @return SelectDataReader
     */
    public function findAll(array $scope = [], array $orderBy = []): SelectDataReader
    {
        return new SelectDataReader($this->select()->where($scope)->orderBy($orderBy));
    }

    /**
     * @param Group $group
     * @param array $scope
     * @param array $orderBy
     * @return SelectDataReader
     */
    public function findAllByGroup(Group $group, array $scope = [], array $orderBy = []): SelectDataReader
    {
        return new SelectDataReader(
            $this
                ->select()
                ->where($scope)
                ->andWhere('groups.id', $group->getId())
                ->orderBy($orderBy)
        );
    }

    /**
     * @param Group $group
     * @param array $scope
     * @param array $orderBy
     * @return SelectDataReader
     */
    public function findActiveByGroup(Group $group, array $scope = [], array $orderBy = []): SelectDataReader
    {
        return new SelectDataReader(
            $this
                ->select()
                ->where($scope)
                ->andWhere('groups.id', $group->getId())
                ->orderBy($orderBy)
        );
    }

    /**
     * @param Group $group
     * @param array $scope
     * @param array $orderBy
     * @return SelectDataReader
     */
    public function findUnconfirmedByGroup(Group $group, array $scope = [], array $orderBy = []): SelectDataReader
    {
        return new SelectDataReader(
            $this
                ->select()
                ->where($scope)
                ->andWhere('groups.id', $group->getId())
                ->orderBy($orderBy)
        );
    }

    /**
     * @param Group $group
     * @param array $scope
     * @param array $orderBy
     * @return SelectDataReader
     */
    public function findUnsubscribedByGroup(Group $group, array $scope = [], array $orderBy = []): SelectDataReader
    {
        return new SelectDataReader(
            $this
                ->select()
                ->where($scope)
                ->andWhere('groups.id', $group->getId())
                ->orderBy($orderBy)
        );
    }

    /**
     * @param Group $group
     * @param array $scope
     * @param array $orderBy
     * @return SelectDataReader
     */
    public function findBouncedByGroup(Group $group, array $scope = [], array $orderBy = []): SelectDataReader
    {
        return new SelectDataReader(
            $this
                ->select()
                ->where($scope)
                ->andWhere('groups.id', $group->getId())
                ->orderBy($orderBy)
        );
    }

    /**
     * @param Group $group
     * @param array $scope
     * @param array $orderBy
     * @return SelectDataReader
     */
    public function findComplaintByGroup(Group $group, array $scope = [], array $orderBy = []): SelectDataReader
    {
        return new SelectDataReader(
            $this
                ->select()
                ->where($scope)
                ->andWhere('groups.id', $group->getId())
                ->orderBy($orderBy)
        );
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
