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
use Mailery\Storage\Filesystem\FileStorageInterface;
use Mailery\Storage\Service\StorageService;
use Mailery\Storage\ValueObject\BucketValueObject;
use Mailery\Storage\ValueObject\FileValueObject;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\ValueObject\ImportValueObject;
use Ramsey\Uuid\Rfc4122\UuidV5;

class ImportService
{
    /**
     * @var ORMInterface
     */
    private ORMInterface $orm;

    /**
     * @var StorageService
     */
    private StorageService $storageService;

    /**
     * @param ORMInterface $orm
     * @param StorageService $storageService
     */
    public function __construct(ORMInterface $orm, StorageService $storageService)
    {
        $this->orm = $orm;
        $this->storageService = $storageService;
    }

    /**
     * @param ImportValueObject $valueObject
     * @return Import
     */
    public function create(ImportValueObject $valueObject): Import
    {
        $file = $this->createFileUniquely($valueObject);

        $fileInfo = $this->storageService->getFileInfo($file);

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
    private function createFileUniquely(ImportValueObject $valueObject, int $tryCount = 0): File
    {
        $brand = $valueObject->getBrand();
        $uuid = UuidV5::fromDateTime(new \DateTimeImmutable('now'))->toString();
        $location = sprintf('/%d/import/subscribers/%s.csv', (int) $brand->getId(), $uuid);

        // TODO: need to use concurently strategy, e.g. mutex or lock file
        try {
            return $this->storageService->create(
                FileValueObject::fromUploadedFile($valueObject->getFile())
                    ->withBrand($brand)
                    ->withLocation($location),
                (new BucketValueObject())
                    ->withBrand($brand)
                    ->withName('subscriber-import')
                    ->withTitle('Subscriber imports')
                    ->withFilesystem(FileStorageInterface::class)
            );
        } catch (FileAlreadyExistsException $e) {
            if ($tryCount === 5) {
                throw $e;
            }

            return $this->createFileUniquely($valueObject, ++$tryCount);
        }
    }
}
