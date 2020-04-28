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

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

/**
 * @Entity(
 *      table = "subscribers_groups",
 *      mapper = "Yiisoft\Yii\Cycle\Mapper\TimestampedMapper"
 * )
 */
class SubscriberGroup
{
    /**
     * @Column(type = "primary")
     * @var int|null
     */
    private $id;
}
