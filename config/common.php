<?php

declare(strict_types=1);

use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Subscriber\Repository\ImportRepository;
use Mailery\Subscriber\Repository\ImportErrorRepository;
use Psr\Container\ContainerInterface;
use Cycle\ORM\ORMInterface;
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Entity\ImportError;

return [
    GroupRepository::class => static function (ContainerInterface $container) {
        return $container
            ->get(ORMInterface::class)
            ->getRepository(Group::class);
    },

    SubscriberRepository::class => static function (ContainerInterface $container) {
        return $container
            ->get(ORMInterface::class)
            ->getRepository(Subscriber::class);
    },

    ImportRepository::class => static function (ContainerInterface $container) {
        return $container
            ->get(ORMInterface::class)
            ->getRepository(Import::class);
    },

    ImportErrorRepository::class => static function (ContainerInterface $container) {
        return $container
            ->get(ORMInterface::class)
            ->getRepository(ImportError::class);
    },
];
