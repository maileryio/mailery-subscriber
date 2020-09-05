<?php

namespace Mailery\Subscriber\Provider;

use Yiisoft\Di\Container;
use Yiisoft\Di\Support\ServiceProvider;
use Yiisoft\Router\RouteCollectorInterface;
use Mailery\Subscriber\Controller\SubscriberController;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Mailery\Subscriber\Controller\ImportController;
use Mailery\Subscriber\Controller\GroupController;
use Mailery\Subscriber\Middleware\AssetBundleMiddleware;

final class RouteCollectorServiceProvider extends ServiceProvider
{
    public function register(Container $container): void
    {
        /** @var RouteCollectorInterface $collector */
        $collector = $container->get(RouteCollectorInterface::class);

        $collector->addGroup(
            Group::create(
                '/brand/{brandId:\d+}',
                [
                    // Subscribers:
                    Route::get('/subscribers', [SubscriberController::class, 'index'])
                        ->name('/subscriber/subscriber/index'),
                    Route::get('/subscriber/subscriber/view/{id:\d+}', [SubscriberController::class, 'view'])
                        ->name('/subscriber/subscriber/view'),
                    Route::methods(['GET', 'POST'], '/subscriber/subscriber/create', [SubscriberController::class, 'create'])
                        ->name('/subscriber/subscriber/create'),
                    Route::methods(['GET', 'POST'], '/subscriber/subscriber/edit/{id:\d+}', [SubscriberController::class, 'edit'])
                        ->name('/subscriber/subscriber/edit'),
                    Route::delete('/subscriber/subscriber/delete/{id:\d+}', [SubscriberController::class, 'delete'])
                        ->name('/subscriber/subscriber/delete'),
                    Route::methods(['GET', 'POST'], '/subscriber/subscriber/import', [SubscriberController::class, 'import'])
                        ->name('/subscriber/subscriber/import'),

                    // Imports:
                    Route::get('/imports', [ImportController::class, 'index'])
                        ->name('/subscriber/import/index'),
                    Route::get('/import/view/{id:\d+}', [ImportController::class, 'view'])
                        ->name('/subscriber/import/view'),

                    // Groups:
                    Route::get('/subscriber/groups', [GroupController::class, 'index'])
                        ->name('/subscriber/group/index'),
                    Route::get('/subscriber/group/view/{id:\d+}', [GroupController::class, 'view'])
                        ->name('/subscriber/group/view'),
                    Route::methods(['GET', 'POST'], '/subscriber/group/create', [GroupController::class, 'create'])
                        ->name('/subscriber/group/create'),
                    Route::methods(['GET', 'POST'], '/subscriber/group/edit/{id:\d+}', [GroupController::class, 'edit'])
                        ->name('/subscriber/group/edit'),
                    Route::delete('/subscriber/group/delete/{id:\d+}', [GroupController::class, 'delete'])
                        ->name('/subscriber/group/delete'),
                    Route::delete('/subscriber/group/delete-subscriber/{id:\d+}/{subscriberId:\d+}', [GroupController::class, 'deleteSubscriber'])
                        ->name('/subscriber/group/delete-subscriber'),
                ]
            )->addMiddleware(AssetBundleMiddleware::class)
        );
    }
}
