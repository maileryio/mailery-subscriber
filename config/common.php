<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

use Mailery\Storage\Filesystem\FileStorageInterface;
use Mailery\Subscriber\Storage\SubscriberImportBucket;
use Psr\Container\ContainerInterface;

return [
    SubscriberImportBucket::class => static function (ContainerInterface $container) {
        return new SubscriberImportBucket($container->get(FileStorageInterface::class));
    },
];
