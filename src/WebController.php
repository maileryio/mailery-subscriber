<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber;

use Cycle\ORM\ORMInterface;
use Mailery\Brand\Service\BrandLocator;
use Mailery\Common\Web\Controller;
use Mailery\Subscriber\Assets\SubscriberAssetBundle;
use Mailery\Web\Assets\AppAssetBundle;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetManager;
use Yiisoft\View\WebView;

abstract class WebController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        AssetManager $assetManager,
        BrandLocator $brandLocator,
        ResponseFactoryInterface $responseFactory,
        Aliases $aliases,
        WebView $view,
        ORMInterface $orm
    ) {
        $bundle = $assetManager->getBundle(AppAssetBundle::class);
        $bundle->depends[] = SubscriberAssetBundle::class;

        parent::__construct($brandLocator, $responseFactory, $aliases, $view, $orm);
    }
}
