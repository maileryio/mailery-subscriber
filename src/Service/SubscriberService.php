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
use Mailery\Common\Counter\Counter;
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\ValueObject\SubscriberValueObject;
use Mailery\Subscriber\Counter\SubscriberCounter;

class SubscriberService
{
    /**
     * @var ORMInterface
     */
    private ORMInterface $orm;

    /**
     * @var SubscriberCounter
     */
    private SubscriberCounter $counter;

    /**
     * @param ORMInterface $orm
     */
    public function __construct(SubscriberCounter $counter, ORMInterface $orm)
    {
        $this->counter = $counter;
        $this->orm = $orm;
    }

    /**
     * @param SubscriberValueObject $valueObject
     * @return Subscriber
     */
    public function create(SubscriberValueObject $valueObject): Subscriber
    {
        $subscriber = (new Subscriber())
            ->setName($valueObject->getName())
            ->setEmail($valueObject->getEmail())
            ->setBrand($valueObject->getBrand())
            ->setConfirmed($valueObject->getConfirmed())
            ->setConfirmed($valueObject->getUnsubscribed())
            ->setConfirmed($valueObject->getBounced())
            ->setConfirmed($valueObject->getComplaint())
        ;

        $tr = new Transaction($this->orm);
        $tr->persist($subscriber);

        $counters = [];

        foreach ($valueObject->getGroups() as $group) {
            $counters[] = $this->counter->withGroup($group);
            $subscriber->getGroups()->add($group);
        }

        $tr->run();

        $counters[] = $this->counter->withBrand($subscriber->getBrand());

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
            ->setName($valueObject->getName())
            ->setEmail($valueObject->getEmail())
            ->setBrand($valueObject->getBrand())
            ->setConfirmed($valueObject->getConfirmed())
            ->setConfirmed($valueObject->getUnsubscribed())
            ->setConfirmed($valueObject->getBounced())
            ->setConfirmed($valueObject->getComplaint())
        ;

        $tr = new Transaction($this->orm);
        $tr->persist($subscriber);

        $counters = [
            'incr' => [],
            'decr' => [],
        ];

        foreach ($subscriber->getGroups() as $group) {
            $subscriber->getGroups()->removeElement($group);
            $counters['decr'][] = $this->counter->withGroup($group);
        }

        foreach ($valueObject->getGroups() as $group) {
            if (!$subscriber->getGroups()->hasPivot($group)) {
                $subscriber->getGroups()->add($group);
                $counters['incr'][] = $this->counter->withGroup($group);
            }
        }

        $tr->run();

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
        $tr = new Transaction($this->orm);

        $counters = [];

        foreach ($subscriber->getGroups() as $groupPivot) {
            if ($group === null || $group === $groupPivot) {
                $counters[] = $this->counter->withGroup($groupPivot);
                $subscriber->getGroups()->removeElement($groupPivot);
            }
        }

        if ($subscriber->getGroups()->count() > 0) {
            $tr->persist($subscriber);
        } else {
            $counters[] = $this->counter->withBrand($subscriber->getBrand());
            $tr->delete($subscriber);
        }

        $tr->run();

        $this->decrCounters($subscriber, $counters);

        return true;
    }

    /**
     * @param Subscriber $subscriber
     * @param Counter[] $counters
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
     * @param Counter[] $counters
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
