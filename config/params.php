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
use Mailery\Subscriber\Controller\GroupController;
use Mailery\Subscriber\Controller\ImportController;
use Mailery\Subscriber\Controller\ReportController;
use Mailery\Subscriber\Controller\SubscriberController;
use Opis\Closure\SerializableClosure;
use Yiisoft\Router\Route;
use Yiisoft\Router\UrlGeneratorInterface;

return [
    'cycle.common' => [
        'entityPaths' => [
            '@vendor/maileryio/mailery-subscriber/src/Entity',
        ],
    ],

    'router' => [
        'routes' => [
            // Subscribers:
            '/subscriber/subscriber/index' => Route::get('/brand/{brandId:\d+}/subscribers', [SubscriberController::class, 'index'])
                ->name('/subscriber/subscriber/index'),
            '/subscriber/subscriber/view' => Route::get('/brand/{brandId:\d+}/subscriber/subscriber/view/{id:\d+}', [SubscriberController::class, 'view'])
                ->name('/subscriber/subscriber/view'),
            '/subscriber/subscriber/create' => Route::methods(['GET', 'POST'], '/brand/{brandId:\d+}/subscriber/subscriber/create', [SubscriberController::class, 'create'])
                ->name('/subscriber/subscriber/create'),
            '/subscriber/subscriber/edit' => Route::methods(['GET', 'POST'], '/brand/{brandId:\d+}/subscriber/subscriber/edit/{id:\d+}', [SubscriberController::class, 'edit'])
                ->name('/subscriber/subscriber/edit'),
            '/subscriber/subscriber/delete' => Route::delete('/brand/{brandId:\d+}/subscriber/subscriber/delete/{id:\d+}', [SubscriberController::class, 'delete'])
                ->name('/subscriber/subscriber/delete'),

            // Imports:
            '/subscriber/import/index' => Route::get('/brand/{brandId:\d+}/imports', [ImportController::class, 'index'])
                ->name('/subscriber/import/index'),

            // Groups:
            '/subscriber/group/index' => Route::get('/brand/{brandId:\d+}/subscriber/groups', [GroupController::class, 'index'])
                ->name('/subscriber/group/index'),
            '/subscriber/group/view' => Route::get('/brand/{brandId:\d+}/subscriber/group/view/{id:\d+}', [GroupController::class, 'view'])
                ->name('/subscriber/group/view'),
            '/subscriber/group/create' => Route::methods(['GET', 'POST'], '/brand/{brandId:\d+}/subscriber/group/create', [GroupController::class, 'create'])
                ->name('/subscriber/group/create'),
            '/subscriber/group/edit' => Route::methods(['GET', 'POST'], '/brand/{brandId:\d+}/subscriber/group/edit/{id:\d+}', [GroupController::class, 'edit'])
                ->name('/subscriber/group/edit'),
            '/subscriber/group/delete' => Route::delete('/brand/{brandId:\d+}/subscriber/group/delete/{id:\d+}', [GroupController::class, 'delete'])
                ->name('/subscriber/group/delete'),
            '/subscriber/group/delete-subscriber' => Route::delete('/brand/{brandId:\d+}/subscriber/group/delete-subscriber/{id:\d+}/{subscriberId:\d+}', [GroupController::class, 'deleteSubscriber'])
                ->name('/subscriber/group/delete-subscriber'),

            // Reports:
            '/subscriber/report/index' => Route::get('/brand/{brandId:\d+}/reports', [ReportController::class, 'index'])
                ->name('/subscriber/report/index'),
        ],
    ],

    'menu' => [
        'sidebar' => [
            'items' => [
                'subscribers' => (new MenuItem())
                    ->withLabel('Subscribers')
                    ->withIcon('account-multiple')
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
                            ])
                            ->withOrder(300),
                        'reports' => (new MenuItem())
                            ->withLabel('Reports')
                            ->withUrl(new SerializableClosure(function (UrlGeneratorInterface $urlGenerator) {
                                return $urlGenerator->generate('/subscriber/report/index');
                            }))
                            ->withActiveRouteNames([
                                '/subscriber/report/index',
                            ])
                            ->withOrder(400),
                    ])
                    ->withOrder(200),
            ],
        ],
    ],
];
