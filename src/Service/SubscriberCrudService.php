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
use Mailery\Subscriber\Counter\SubscriberCounter;
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
     * @param SubscriberCounter $counter
     */
    public function __construct(
        private ORMInterface $orm,
        private SubscriberCounter $counter
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

        $counters = [];
        foreach ($valueObject->getGroups() as $group) {
            $counters[] = $this->counter->withGroup($group);
            $subscriber->getGroups()->add($group);
        }

        $counters[] = $this->counter->withBrand($subscriber->getBrand());

        (new EntityWriter($this->orm))->write([$subscriber]);

        $this->incrCounters($subscriber, $counters);

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

        $counters = [
            'incr' => [],
            'decr' => [],
        ];

        foreach ($subscriber->getGroups() as $group) {
            $subscriber->getGroups()->removeElement($group);
            $counters['decr'][] = $this->counter->withGroup($group);
        }

        foreach ($valueObject->getGroups() as $group) {
            if (!$subscriber->getGroups()->contains($group)) {
                $subscriber->getGroups()->add($group);
                $counters['incr'][] = $this->counter->withGroup($group);
            }
        }

        (new EntityWriter($this->orm))->write([$subscriber]);

        $this->decrCounters($subscriber, $counters['decr']);
        $this->incrCounters($subscriber, $counters['incr']);

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

        $counters = [];

        foreach ($subscriber->getGroups() as $groupPivot) {
            if ($group === null || $group === $groupPivot) {
                $counters[] = $this->counter->withGroup($groupPivot);
                $subscriber->getGroups()->removeElement($groupPivot);
            }
        }

        if ($subscriber->getGroups()->count() > 0) {
            $transaction->persist($subscriber);
        } else {
            $counters[] = $this->counter->withBrand($subscriber->getBrand());
            $transaction->delete($subscriber);
        }

        $transaction->run();

        $this->decrCounters($subscriber, $counters);

        return true;
    }

    /**
     * @param Subscriber $subscriber
     * @param SubscriberCounter[] $counters
     */
    private function incrCounters(Subscriber $subscriber, array $counters)
    {
        foreach ($counters as $counter) {
            $counter->incrTotalCount();

            if ($subscriber->isConfirmed()) {
                $counter->incrConfirmedCount();
            }
            if ($subscriber->isUnsubscribed()) {
                $counter->incrUnsubscribedCount();
            }
            if ($subscriber->isBounced()) {
                $counter->incrBouncedCount();
            }
            if ($subscriber->isComplaint()) {
                $counter->incrComplaintCount();
            }
        }
    }

    /**
     * @param Subscriber $subscriber
     * @param SubscriberCounter[] $counters
     */
    private function decrCounters(Subscriber $subscriber, array $counters)
    {
        foreach ($counters as $counter) {
            $counter->decrTotalCount();

            if ($subscriber->isConfirmed()) {
                $counter->decrConfirmedCount();
            }
            if ($subscriber->isUnsubscribed()) {
                $counter->decrUnsubscribedCount();
            }
            if ($subscriber->isBounced()) {
                $counter->decrBouncedCount();
            }
            if ($subscriber->isComplaint()) {
                $counter->decrComplaintCount();
            }
        }
    }
}
