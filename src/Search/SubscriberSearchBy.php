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

use Mailery\Widget\Search\Model\SearchBy;

class SubscriberSearchBy extends SearchBy
{
    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            self::getOperator(),
            [
                ['like', 'name', '%' . $this->getSearchPhrase() . '%'],
                ['like', 'email', '%' . $this->getSearchPhrase() . '%'],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getOperator(): string
    {
        return 'or';
    }
}
