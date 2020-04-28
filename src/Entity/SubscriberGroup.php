<?php

declare(strict_types=1);

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
