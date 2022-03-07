<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Service;

use Cycle\ORM\ORMInterface;
use Mailery\Storage\Entity\File;
use Mailery\Storage\Service\StorageService;
use Mailery\Storage\ValueObject\FileValueObject;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\ValueObject\ImportValueObject;
use Mailery\Subscriber\Model\SubscriberImportBucket;
use Mailery\Storage\Filesystem\FileInfo;
use Mailery\Brand\Entity\Brand;
use Yiisoft\Yii\Cycle\Data\Writer\EntityWriter;

class ImportCrudService
{
    /**
     * @var ORMInterface
     */
    private ORMInterface $orm;

    /**
     * @var SubscriberImportBucket
     */
    private SubscriberImportBucket $bucket;

    /**
     * @var FileInfo
     */
    private FileInfo $fileInfo;

    /**
     * @var StorageService
     */
    private StorageService $storageService;

    /**
     * @var Brand
     */
    private Brand $brand;

    /**
     * @param ORMInterface $orm
     * @param SubscriberImportBucket $bucket
     * @param FileInfo $fileInfo
     * @param StorageService $storageService
     */
    public function __construct(
        ORMInterface $orm,
        SubscriberImportBucket $bucket,
        FileInfo $fileInfo,
        StorageService $storageService
    ) {
        $this->orm = $orm;
        $this->bucket = $bucket;
        $this->fileInfo = $fileInfo;
        $this->storageService = $storageService;
    }

    /**
     * @param Brand $brand
     * @return self
     */
    public function withBrand(Brand $brand): self
    {
        $new = clone $this;
        $new->brand = $brand;

        return $new;
    }

    /**
     * @param ImportValueObject $valueObject
     * @return Import
     */
    public function create(ImportValueObject $valueObject): Import
    {
        $file = $this->createFile($valueObject);

        $import = (new Import())
            ->setBrand($this->brand)
            ->setFile($file)
            ->setFieldsMap($valueObject->getFieldsMap())
            ->setTotalCount($this->getLineCount($file))
            ->setIsPending()
        ;

        foreach ($valueObject->getGroups() as $group) {
            $import->getGroups()->add($group);
        }

        (new EntityWriter($this->orm))->write([$import]);

        return $import;
    }

    /**
     * @param Import $import
     * @return bool
     */
    public function delete(Import $import): bool
    {
        (new EntityWriter($this->orm))->delete([$import]);

        return true;
    }

    /**
     * @param ImportValueObject $valueObject
     * @return File
     */
    private function createFile(ImportValueObject $valueObject): File
    {
        return $this->storageService
            ->withBrand($this->brand)
            ->create(
                FileValueObject::fromUploadedFile($valueObject->getFile())
                    ->withBucket($this->bucket)
            );
    }

    /**
     * @param File $file
     * @return int
     */
    private function getLineCount(File $file): int
    {
        $stream = $this->fileInfo
            ->withFile($file)
            ->getStream()
            ->detach();

        $lineCount = 0;

        while (!feof($stream)) {
            if (($line = fgets($stream)) !== false) {
                $lineCount = $lineCount + substr_count($line, PHP_EOL);
            }
        }
        fclose($stream);

        return $lineCount;
    }
}
