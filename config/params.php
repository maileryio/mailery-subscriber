<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Definitions\Reference;
use Mailery\Subscriber\Model\SubscriberImportBucket;
use Yiisoft\Definitions\DynamicReference;
use Mailery\Messenger\Transport\BeanstalkdTransportFactory;
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Entity\ImportError;
use Mailery\Subscriber\Entity\ImportGroup;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Entity\SubscriberGroup;
use Mailery\Subscriber\Messenger\Message\ImportSubscribers;
use Mailery\Subscriber\Messenger\Handler\ImportSubscribersHandler;

return [
    'yiisoft/yii-cycle' => [
        'entity-paths' => [
            '@vendor/maileryio/mailery-subscriber/src/Entity',
        ],
    ],

    'maileryio/mailery-activity-log' => [
        'entity-groups' => [
            'subscriber' => [
                'label' => DynamicReference::to(static fn () => 'Subscriber'),
                'entities' => [
                    Group::class,
                    Import::class,
                    ImportError::class,
                    ImportGroup::class,
                    Subscriber::class,
                    SubscriberGroup::class,
                ],
            ],
        ],
    ],

    'maileryio/mailery-messenger' => [
        'handlers' => [
            ImportSubscribers::class => [ImportSubscribersHandler::class],
        ],
        'senders' => [
            ImportSubscribers::class => ['subscriber'],
        ],
        'recievers' => [
            'subscriber' => [
                'transport' => DynamicReference::to(new BeanstalkdTransportFactory([
                    'tube_name' => 'subscriber',
                ])),
            ],
        ],
    ],

    'maileryio/mailery-storage' => [
        'buckets' => [
            Reference::to(SubscriberImportBucket::class),
        ],
    ],

    'maileryio/mailery-menu-sidebar' => [
        'items' => [
            'subscribers' => [
                'label' => static function () {
                    return 'Subscribers';
                },
                'icon' => 'account-multiple-outline',
                'items' => [
                    'subscribers' => [
                        'label' => static function () {
                            return 'All Subscribers';
                        },
                        'url' => static function (UrlGeneratorInterface $urlGenerator) {
                            return $urlGenerator->generate('/subscriber/subscriber/index');
                        },
                        'activeRouteNames' => [
                            '/subscriber/subscriber/index',
                            '/subscriber/subscriber/view',
                            '/subscriber/subscriber/create',
                            '/subscriber/subscriber/edit',
                            '/subscriber/subscriber/import',
                        ],
                    ],
                    'groups' => [
                        'label' => static function () {
                            return 'Groups & Segments';
                        },
                        'url' => static function (UrlGeneratorInterface $urlGenerator) {
                            return $urlGenerator->generate('/subscriber/group/index');
                        },
                        'activeRouteNames' => [
                            '/subscriber/group/index',
                            '/subscriber/group/view',
                            '/subscriber/group/create',
                            '/subscriber/group/edit',
                        ],
                    ],
                    'imports' => [
                        'label' => static function () {
                            return 'Import Lists';
                        },
                        'url' => static function (UrlGeneratorInterface $urlGenerator) {
                            return $urlGenerator->generate('/subscriber/import/index');
                        },
                        'activeRouteNames' => [
                            '/subscriber/import/index',
                            '/subscriber/import/view',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
