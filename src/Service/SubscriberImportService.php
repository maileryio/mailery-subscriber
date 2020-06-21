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
use Mailery\Subscriber\Entity\SubscriberImport;
use Mailery\Subscriber\ValueObject\SubscriberImportValueObject;

class SubscriberImportService
{
    /**
     * @var ORMInterface
     */
    private ORMInterface $orm;

    /**
     * @param ORMInterface $orm
     */
    public function __construct(ORMInterface $orm)
    {
        $this->orm = $orm;
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
