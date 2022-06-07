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
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\ValueObject\SubscriberValueObject;
use Mailery\Brand\Entity\Brand;
use Yiisoft\Yii\Cycle\Data\Writer\EntityWriter;

class SubscriberCrudService
{

    /**
     * @var Brand
     */
    private Brand $brand;

    /**
     * @param ORMInterface $orm
     */
    public function __construct(
        private ORMInterface $orm,
    ) {}

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
     * @param SubscriberValueObject $valueObject
     * @return Subscriber
     */
    public function create(SubscriberValueObject $valueObject): Subscriber
    {
        $subscriber = (new Subscriber())
            ->setBrand($this->brand)
            ->setName($valueObject->getName())
            ->setEmail($valueObject->getEmail())
            ->setConfirmed($valueObject->getConfirmed())
            ->setUnsubscribed($valueObject->getUnsubscribed())
            ->setBounced($valueObject->getBounced())
            ->setComplaint($valueObject->getComplaint())
        ;

        foreach ($valueObject->getGroups() as $group) {
            $subscriber->getGroups()->add($group);
        }

        (new EntityWriter($this->orm))->write([$subscriber]);

        return $subscriber;
    }

    /**
     * @param Subscriber $subscriber
     * @param SubscriberValueObject $valueObject
     * @return Subscriber
     */
    public function update(Subscriber $subscriber, SubscriberValueObject $valueObject): Subscriber
    {
        $subscriber = $subscriber
            ->setBrand($this->brand)
            ->setName($valueObject->getName())
            ->setEmail($valueObject->getEmail())
            ->setConfirmed($valueObject->getConfirmed())
            ->setUnsubscribed($valueObject->getUnsubscribed())
            ->setBounced($valueObject->getBounced())
            ->setComplaint($valueObject->getComplaint())
        ;

        foreach ($subscriber->getGroups() as $group) {
            $subscriber->getGroups()->removeElement($group);
        }

        foreach ($valueObject->getGroups() as $group) {
            if (!$subscriber->getGroups()->contains($group)) {
                $subscriber->getGroups()->add($group);
            }
        }

        (new EntityWriter($this->orm))->write([$subscriber]);

        return $subscriber;
    }

    /**
     * @param Subscriber $subscriber
     * @param Group|null $group
     * @return bool
     */
    public function delete(Subscriber $subscriber, Group $group = null): bool
    {
        $transaction = new Transaction($this->orm);

        foreach ($subscriber->getGroups() as $groupPivot) {
            if ($group === null || $group === $groupPivot) {
                $subscriber->getGroups()->removeElement($groupPivot);
            }
        }

        if ($subscriber->getGroups()->count() > 0) {
            $transaction->persist($subscriber);
        } else {
            $transaction->delete($subscriber);
        }

        $transaction->run();

        return true;
    }

}
