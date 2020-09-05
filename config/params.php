<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

use Mailery\Menu\MenuItem;
use Opis\Closure\SerializableClosure;
use Yiisoft\Router\UrlGeneratorInterface;

return [
    'yiisoft/yii-cycle' => [
        'annotated-entity-paths' => [
            '@vendor/maileryio/mailery-subscriber/src/Entity',
        ],
    ],

    'menu' => [
        'sidebar' => [
            'items' => [
                'subscribers' => (new MenuItem())
                    ->withLabel('Subscribers')
                    ->withIcon('account-multiple-outline')
                    ->withChildItems([
                        'subscribers' => (new MenuItem())
                            ->withLabel('All Subscribers')
                            ->withUrl(new SerializableClosure(function (UrlGeneratorInterface $urlGenerator) {
                                return $urlGenerator->generate('/subscriber/subscriber/index');
                            }))
                            ->withActiveRouteNames([
                                '/subscriber/subscriber/index',
                                '/subscriber/subscriber/view',
                                '/subscriber/subscriber/create',
                                '/subscriber/subscriber/edit',
                                '/subscriber/subscriber/delete',
                                '/subscriber/subscriber/import',
                            ])
                            ->withOrder(100),
                        'groups' => (new MenuItem())
                            ->withLabel('Groups & Segments')
                            ->withUrl(new SerializableClosure(function (UrlGeneratorInterface $urlGenerator) {
                                return $urlGenerator->generate('/subscriber/group/index');
                            }))
                            ->withActiveRouteNames([
                                '/subscriber/group/index',
                                '/subscriber/group/view',
                                '/subscriber/group/create',
                                '/subscriber/group/edit',
                                '/subscriber/group/delete',
                            ])
                            ->withOrder(200),
                        'imports' => (new MenuItem())
                            ->withLabel('Import Lists')
                            ->withUrl(new SerializableClosure(function (UrlGeneratorInterface $urlGenerator) {
                                return $urlGenerator->generate('/subscriber/import/index');
                            }))
                            ->withActiveRouteNames([
                                '/subscriber/import/index',
                                '/subscriber/import/view',
                            ])
                            ->withOrder(300),
                    ])
                    ->withOrder(300),
            ],
        ],
    ],
];
