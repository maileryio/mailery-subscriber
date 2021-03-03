<?php

namespace Mailery\Subscriber\Model;

use Mailery\Storage\BucketInterface;
use Yiisoft\Yii\Filesystem\FilesystemInterface;
use Mailery\Brand\BrandLocator;

class SubscriberImportBucket implements BucketInterface
{
    /**
     * @var FilesystemInterface
     */
    private FilesystemInterface $filesystem;

    /**
     * @var BrandLocator
     */
    private BrandLocator $brandLocator;

    /**
     * @param FilesystemInterface $filesystem
     * @param BrandLocator $brandLocator
     */
    public function __construct(FilesystemInterface $filesystem, BrandLocator $brandLocator)
    {
        $this->filesystem = $filesystem;
        $this->brandLocator = $brandLocator;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::class;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return sprintf('/%d/subscriber/import', $this->brandLocator->getBrand()->getId());
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Subscriber imports';
    }

    /**
     * @return FilesystemInterface
     */
    public function getFilesystem(): FilesystemInterface
    {
        return $this->filesystem;
    }
}
