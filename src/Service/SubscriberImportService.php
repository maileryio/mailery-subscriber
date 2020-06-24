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
use Mailery\Storage\Service\StorageService;
use Mailery\Storage\ValueObject\BucketValueObject;
use Mailery\Storage\ValueObject\FileValueObject;
use Mailery\Subscriber\Entity\SubscriberImport;
use Mailery\Subscriber\ValueObject\SubscriberImportValueObject;

class SubscriberImportService
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
     * @param SubscriberImportValueObject $valueObject
     * @return SubscriberImport
     */
    public function create(SubscriberImportValueObject $valueObject): SubscriberImport
    {
        $file = $this->storageService->create(
            FileValueObject::fromUploadedFile($valueObject->getFile())
                ->withBrand($valueObject->getBrand())
                ->withLocation('/import/subscribers/' . uniqid()),
            (new BucketValueObject())
                ->withBrand($valueObject->getBrand())
                ->withName('subscriber-import')
                ->withTitle('Subscriber import lists')
        );

        $import = (new SubscriberImport())
            ->setBrand($valueObject->getBrand())
            ->setFile($file)
            ->setFieldsMap($valueObject->getFieldsMap())
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
