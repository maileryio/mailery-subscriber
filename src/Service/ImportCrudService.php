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
use Mailery\Storage\Exception\FileAlreadyExistsException;
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
     * @var StorageService
     */
    private StorageService $storageService;

    /**
     * @var FileInfo
     */
    private FileInfo $fileInfo;

    /**
     * @param ORMInterface $orm
     * @param SubscriberImportBucket $bucket
     * @param StorageService $storageService
     * @param FileInfo $fileInfo
     */
    public function __construct(
        ORMInterface $orm,
        SubscriberImportBucket $bucket,
        StorageService $storageService,
        FileInfo $fileInfo
    ) {
        $this->orm = $orm;
        $this->bucket = $bucket;
        $this->storageService = $storageService;
        $this->fileInfo = $fileInfo;
    }

    /**
     * @param ImportValueObject $valueObject
     * @return Import
     */
    public function create(ImportValueObject $valueObject): Import
    {
        $file = $this->createFile($valueObject);

        $fileInfo = $this->fileInfo->withFile($file);

        $import = (new Import())
            ->setBrand($valueObject->getBrand())
            ->setFile($file)
            ->setFieldsMap($valueObject->getFieldsMap())
            ->setTotalCount($fileInfo->getLineCount())
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
     * @param int $tryCount
     * @return File
     */
    private function createFile(ImportValueObject $valueObject, int $tryCount = 0): File
    {
        try {
            return $this->storageService->create(
                FileValueObject::fromUploadedFile($valueObject->getFile())
                    ->withBrand($valueObject->getBrand())
                    ->withBucket($this->bucket)
            );
        } catch (FileAlreadyExistsException $e) {
            if ($tryCount === 5) {
                throw $e;
            }

            return $this->createFile($valueObject, ++$tryCount);
        }
    }
}
