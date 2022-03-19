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
use Mailery\Brand\Entity\Brand;
use Mailery\Subscriber\Counter\ImportCounter;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Entity\ImportError;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Importer\InterpreterInterface;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Subscriber\Service\SubscriberCrudService;
use Mailery\Subscriber\ValueObject\SubscriberValueObject;
use Yiisoft\Validator\Validator;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Yii\Cycle\Data\Writer\EntityWriter;

class SubscriberInterpreter implements InterpreterInterface
{
    /**
     * @var Import
     */
    private Import $import;

    /**
     * @param ORMInterface $orm
     * @param SubscriberCrudService $subscriberCrudService
     * @param ImportCounter $importCounter
     */
    public function __construct(
        private ORMInterface $orm,
        private SubscriberCrudService $subscriberCrudService,
        private ImportCounter $importCounter
    ) {}

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
     * @inheritdoc
     */
    public function interpret($line): void
    {
        $attributes = [];

        foreach ($this->import->getFields() as $field => $column) {
            $attributes[$field] = $line[$column] ?? null;
        }

        $valueObject = SubscriberValueObject::fromArray($attributes)
            ->withGroups($this->import->getGroups()->toArray());

        $hasErrors = false;
        foreach ($this->validate($valueObject)->getErrorMessagesIndexedByAttribute() as $attribute => $errors) {
            $hasErrors = true;

            $this->flushError(
                $attribute,
                $valueObject->getAttributeValue($attribute),
                $errors[0] ?? 'Unknown error'
            );
        }

        $this->flushSubscriberValueObject($valueObject, $hasErrors);
    }

    /**
     * @param DataSetInterface $valueObject
     * @return Result
     */
    private function validate(DataSetInterface $valueObject): Result
    {
        return (new Validator())
            ->validate(
                $valueObject,
                [
                    'email' => [
                        Required::rule(),
                        Email::rule(),
                        HasLength::rule()->max(255),
                    ],
                    'name' => [
                        Required::rule(),
                        HasLength::rule()->min(3)->max(255),
                    ],
                ]
            );
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

        (new EntityWriter($this->orm))->write([$error]);
    }

    /**
     * @param SubscriberValueObject $valueObject
     * @param bool $hasErrors
     * @return void
     */
    private function flushSubscriberValueObject(SubscriberValueObject $valueObject, bool $hasErrors): void
    {
        $repo = $this->getSubscriberRepository($this->import->getBrand());
        $counter = $this->importCounter->withImport($this->import);

        if ($hasErrors) {
            $counter->incrSkippedCount();
            return;
        }

        $subscriberCrudService = $this->subscriberCrudService->withBrand($this->import->getBrand());

        if (($subscriber = $repo->findByEmail($valueObject->getEmail())) === null) {
            $subscriberCrudService->create($valueObject);
            $counter->incrInsertedCount();
        } else {
            $subscriberCrudService->update($subscriber, $valueObject);
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
