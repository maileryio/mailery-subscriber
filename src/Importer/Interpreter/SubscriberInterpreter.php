<?php

namespace Mailery\Subscriber\Importer\Interpreter;

use Mailery\Subscriber\Importer\InterpreterInterface;
use Mailery\Subscriber\Service\SubscriberService;
use Mailery\Subscriber\Validator\SubscriberValidator;
use Mailery\Subscriber\ValueObject\SubscriberValueObject;
use Cycle\ORM\ORMInterface;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Subscriber\Entity\SubscriberImport;
use Mailery\Subscriber\Entity\SubscriberImportError;
use Cycle\ORM\Transaction;
use Mailery\Brand\Entity\Brand;

class SubscriberInterpreter implements InterpreterInterface
{
    /**
     * @var array
     */
    private array $rows = [];

    /**
     * @var SubscriberImport
     */
    private SubscriberImport $import;

    /**
     * @var ORMInterface
     */
    private ORMInterface $orm;

    /**
     * @var SubscriberService
     */
    private SubscriberService $subscriberService;

    /**
     * @var SubscriberValidator
     */
    private SubscriberValidator $subscriberValidator;

    /**
     * @param ORMInterface $orm
     * @param SubscriberService $subscriberService
     * @param SubscriberValidator $subscriberValidator
     */
    public function __construct(ORMInterface $orm, SubscriberService $subscriberService, SubscriberValidator $subscriberValidator)
    {
        $this->orm = $orm;
        $this->subscriberService = $subscriberService;
        $this->subscriberValidator = $subscriberValidator;
    }

    /**
     * @param SubscriberImport $import
     * @return self
     */
    public function withImport(SubscriberImport $import): self
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

        foreach ($this->import->getFieldsMap() as $field => $column) {
            $attributes[$field] = $line[$column] ?? null;
        }

        $hasErrors = false;

        $valueObject = SubscriberValueObject::fromArray($attributes)
            ->withBrand($this->import->getBrand())
            ->withGroups($this->import->getGroups()->toArray());

        $results = $this->subscriberValidator->validate($valueObject);
        foreach ($results as $attribute => $result) {
            if ($result->isValid() === false) {
                $hasErrors = true;

                $this->flushError(
                    $attribute,
                    $valueObject->getAttributeValue($attribute),
                    $result->getErrors()[0] ?? 'Unknown error'
                );
            }
        }

        if (!$hasErrors) {
            $this->flushSubscriberValueObject($valueObject);
        }
    }

    /**
     * @param string $attribute
     * @param string|null $value
     * @param string $message
     * @return void
     */
    private function flushError(string $attribute, ?string $value, string $message): void
    {
        $error = (new SubscriberImportError())
            ->setImport($this->import)
            ->setName($attribute)
            ->setValue($value)
            ->setError($message);

        $transaction = new Transaction($this->orm);
        $transaction->persist($error);
        $transaction->run();
    }

    /**
     * @param SubscriberValueObject $valueObject
     * @return void
     */
    private function flushSubscriberValueObject(SubscriberValueObject $valueObject): void
    {
        $repo = $this->getSubscriberRepository($valueObject->getBrand());

        if (($subscriber = $repo->findByEmail($valueObject->getEmail())) === null) {
            $this->subscriberService->create($valueObject);
        } else {
            $this->subscriberService->update($subscriber, $valueObject);
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
