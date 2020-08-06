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

use Cycle\ORM\Select\Repository;
use Mailery\Subscriber\Entity\Import;
use Mailery\Widget\Search\Data\Reader\SelectDataReader;

class ImportErrorRepository extends Repository
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
     * @param Import $import
     * @return self
     */
    public function withImport(Import $import): self
    {
        $repo = clone $this;
        $repo->select
            ->andWhere([
                'subscriber_import_id' => $import->getId(),
            ]);

        return $repo;
    }
}
