<?php

namespace Mailery\Subscriber\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Assets\AssetManager;
use Mailery\Web\Assets\AppAssetBundle;
use Mailery\Subscriber\Assets\SubscriberAssetBundle;

class AssetBundleMiddleware implements MiddlewareInterface
{
    /**
     * @var AssetManager
     */
    private AssetManager $assetManager;

    /**
     * @param AssetManager $assetManager
     */
    public function __construct(AssetManager $assetManager)
    {
        $this->assetManager = $assetManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $bundle = $this->assetManager->getBundle(AppAssetBundle::class);
        $bundle->depends[] = SubscriberAssetBundle::class;

        return $handler->handle($request);
    }
}
