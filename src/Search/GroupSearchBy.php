<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Search;

use Cycle\ORM\Select;
use Cycle\ORM\Select\QueryBuilder;
use Mailery\Widget\Search\Model\SearchBy;

class GroupSearchBy extends SearchBy
{
    /**
     * {@inheritdoc}
     */
    protected function buildQueryInternal(Select $query, string $searchPhrase): Select
    {
        $newQuery = clone $query;

        $newQuery->andWhere(function (QueryBuilder $select) use ($searchPhrase) {
            return $select
                ->andWhere(['name' => ['like' => '%' . $searchPhrase . '%']]);
        });

        return $newQuery;
    }
}
