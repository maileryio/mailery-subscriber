<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Importer\Interpreter;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Transaction;
use Mailery\Brand\Entity\Brand;
use Mailery\Subscriber\Counter\ImportCounter;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Entity\ImportError;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Factory\ValidatorFactory;
use Mailery\Subscriber\Importer\InterpreterInterface;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Subscriber\Service\SubscriberCrudService;
use Mailery\Subscriber\ValueObject\SubscriberValueObject;
use Yiisoft\Validator\Validator;

class SubscriberInterpreter implements InterpreterInterface
{
    /**
     * @var array
     */
    private array $rows = [];

    /**
     * @var Import
     */
    private Import $import;

    /**
     * @var ORMInterface
     */
    private ORMInterface $orm;

    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * @var SubscriberCrudService
     */
    private SubscriberCrudService $subscriberCrudService;

    /**
     * @var ImportCounter
     */
    private ImportCounter $importCounter;

    /**
     * @param ORMInterface $orm
     * @param ValidatorFactory $validatorFactory
     * @param SubscriberCrudService $subscriberCrudService
     * @param ImportCounter $importCounter
     */
    public function __construct(ORMInterface $orm, ValidatorFactory $validatorFactory, SubscriberCrudService $subscriberCrudService, ImportCounter $importCounter)
    {
        $this->orm = $orm;
        $this->validator = $validatorFactory->createSubscriberValidator();
        $this->subscriberCrudService = $subscriberCrudService;
        $this->importCounter = $importCounter;
    }

    /**
     * @param Import $import
     * @return self
     */
    public function withImport(Import $import): self
    {
        $new = clone $this;
        $new->import = $import;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function interpret($line): void
    {
        $attributes = [];

        foreach ($this->import->getFieldsMap() as $field => $column) {
            $attributes[$field] = $line[$column] ?? null;
        }

        $valueObject = SubscriberValueObject::fromArray($attributes)
            ->withBrand($this->import->getBrand())
            ->withGroups($this->import->getGroups()->toArray());

        $hasErrors = false;
        $results = $this->validator->validate($valueObject);

        foreach ($results as $attribute => $result) {
            if (is_string($attribute) && $result->isValid() === false) {
                $hasErrors = true;

                $this->flushError(
                    $attribute,
                    $valueObject->getAttributeValue($attribute),
                    $result->getErrors()[0] ?? 'Unknown error'
                );
            }
        }

        $this->flushSubscriberValueObject($valueObject, $hasErrors);
    }

    /**
     * @param string $attribute
     * @param string|null $value
     * @param string $message
     * @return void
     */
    private function flushError(string $attribute, ?string $value, string $message): void
    {
        $error = (new ImportError())
            ->setImport($this->import)
            ->setName($attribute)
            ->setError($message);

        if ($value) {
            $error->setValue($value);
        }

        $transaction = new Transaction($this->orm);
        $transaction->persist($error);
        $transaction->run();
    }

    /**
     * @param SubscriberValueObject $valueObject
     * @param bool $hasErrors
     * @return void
     */
    private function flushSubscriberValueObject(SubscriberValueObject $valueObject, bool $hasErrors): void
    {
        $repo = $this->getSubscriberRepository($valueObject->getBrand());
        $counter = $this->importCounter->withImport($this->import);

        if ($hasErrors) {
            $counter->incrSkippedCount();

            return;
        }

        if (($subscriber = $repo->findByEmail($valueObject->getEmail())) === null) {
            $this->subscriberCrudService->create($valueObject);
            $counter->incrInsertedCount();
        } else {
            $this->subscriberCrudService->update($subscriber, $valueObject);
            $counter->incrUpdatedCount();
        }
    }

    /**
     * @param Brand $brand
     * @return SubscriberRepository
     */
    private function getSubscriberRepository(Brand $brand): SubscriberRepository
    {
        return $this->orm->getRepository(Subscriber::class)
            ->withBrand($brand);
    }
}
