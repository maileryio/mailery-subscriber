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
use Mailery\Widget\Search\Data\Reader\SelectDataReader;

class GroupRepository extends Repository
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
