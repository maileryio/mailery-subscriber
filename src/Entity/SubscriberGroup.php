<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Entity;

/**
 * @Cycle\Annotated\Annotation\Entity(
 *      table = "subscribers_groups",
 *      mapper = "Mailery\Subscriber\Mapper\DefaultMapper"
 * )
 */
class SubscriberGroup
{
    /**
     * @Cycle\Annotated\Annotation\Column(type = "primary")
     * @var int|null
     */
    private $id;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'SubscriberGroup';
    }
}
