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
use Mailery\Storage\Service\FileService;
use Mailery\Storage\ValueObject\FileValueObject;
use Mailery\Subscriber\Entity\SubscriberImport;
use Mailery\Subscriber\Storage\SubscriberImportBucket;
use Mailery\Subscriber\ValueObject\SubscriberImportValueObject;

class SubscriberImportService
{
    /**
     * @var ORMInterface
     */
    private ORMInterface $orm;

    /**
     * @var SubscriberImportBucket
     */
    private SubscriberImportBucket $fileBucket;

    /**
     * @var FileService
     */
    private FileService $fileService;

    /**
     * @param ORMInterface $orm
     * @param SubscriberImportBucket $fileBucket
     * @param FileService $fileService
     */
    public function __construct(ORMInterface $orm, SubscriberImportBucket $fileBucket, FileService $fileService)
    {
        $this->orm = $orm;
        $this->fileBucket = $fileBucket;
        $this->fileService = $fileService;
    }

    /**
     * @param SubscriberImportValueObject $valueObject
     * @return SubscriberImport
     */
    public function create(SubscriberImportValueObject $valueObject): SubscriberImport
    {
        $import = (new SubscriberImport())
            ->setBrand($valueObject->getBrand())
        ;

        $tr = new Transaction($this->orm);
        $tr->persist($import);
        $tr->run();

        $file = $this->fileService->create(
            (new FileValueObject())
                ->withUploadedFile($valueObject->getFile())
                ->withFilePath($import->getFilePath())
                ->withFileBucket($this->fileBucket)
                ->withBrand($valueObject->getBrand())
        );

        $import->setFile($file);

        $tr->persist($import);
        $tr->run();

        return $import;
    }

    /**
     * @param SubscriberImport $import
     * @param SubscriberImportValueObject $valueObject
     * @return SubscriberImport
     */
    public function update(SubscriberImport $import, SubscriberImportValueObject $valueObject): SubscriberImport
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
     * @param SubscriberImport $import
     * @return bool
     */
    public function delete(SubscriberImport $import): bool
    {
        $tr = new Transaction($this->orm);
        $tr->delete($import);
        $tr->run();

        return true;
    }
}
