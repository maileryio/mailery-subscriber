<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

use Cycle\ORM\ORMInterface;
use Mailery\Storage\Factory\StorageFactory;
use Mailery\Storage\Filesystem\FileStorageInterface;
use Mailery\Subscriber\Service\SubscriberImportService;
use Psr\Container\ContainerInterface;

return [
    SubscriberImportService::class => static function (ContainerInterface $container) {
        $orm = $container->get(ORMInterface::class);
        $storageFactory = $container->get(StorageFactory::class);
        $fileStorage = $container->get(FileStorageInterface::class);

        return new SubscriberImportService(
            $orm,
            $storageFactory->withFilesystem($fileStorage)->create()
        );
    },
];
