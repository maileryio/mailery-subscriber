<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Queue;

use Yiisoft\Yii\Queue\Job\JobInterface;
use Mailery\Subscriber\Entity\SubscriberImport;

class SubscriberImportJob implements JobInterface
{
    /**
     * @var SubscriberImport
     */
    private SubscriberImport $import;

    /**
     * @param SubscriberImport $import
     */
    public function __construct(SubscriberImport $import)
    {
        $this->import = $import;
    }

    public function execute()
    {
        var_dump(1111);exit;
    }
}
