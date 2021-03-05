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
use Cycle\ORM\Transaction;
use Mailery\Storage\Entity\File;
use Mailery\Storage\Service\StorageService;
use Mailery\Storage\ValueObject\FileValueObject;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\ValueObject\ImportValueObject;
use Mailery\Subscriber\Model\SubscriberImportBucket;
use Mailery\Storage\Filesystem\FileInfo;

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
     * @param ImportValueObject $valueObject
     * @return Import
     */
    public function create(ImportValueObject $valueObject): Import
    {
        $file = $this->createFile($valueObject);

        $import = (new Import())
            ->setBrand($valueObject->getBrand())
            ->setFile($file)
            ->setFieldsMap($valueObject->getFieldsMap())
            ->setTotalCount($this->getLineCount($file))
            ->setIsPending()
        ;

        foreach ($valueObject->getGroups() as $group) {
            $import->getGroups()->add($group);
        }

        $tr = new Transaction($this->orm);
        $tr->persist($import);
        $tr->run();

        return $import;
    }

    /**
     * @param Import $import
     * @param ImportValueObject $valueObject
     * @return Import
     */
    public function update(Import $import, ImportValueObject $valueObject): Import
    {
        $import = $import
            ->setBrand($valueObject->getBrand())
        ;

        $tr = new Transaction($this->orm);
        $tr->persist($import);
        $tr->run();

        return $import;
    }

    /**
     * @param Import $import
     * @return bool
     */
    public function delete(Import $import): bool
    {
        $tr = new Transaction($this->orm);
        $tr->delete($import);
        $tr->run();

        return true;
    }

    /**
     * @param ImportValueObject $valueObject
     * @return File
     */
    private function createFile(ImportValueObject $valueObject): File
    {
        return $this->storageService->create(
            FileValueObject::fromUploadedFile($valueObject->getFile())
                ->withBrand($valueObject->getBrand())
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
