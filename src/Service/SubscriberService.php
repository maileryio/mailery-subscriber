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

class SubscriberService
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

        foreach ($valueObject->getGroups() as $group) {
            $subscriber->getGroups()->add($group);
            $group->incrTotalCount();
            $tr->persist($group);
        }

        $tr->run();

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

        foreach ($subscriber->getGroups() as $group) {
            if ($subscriber->getGroups()->hasPivot($group)) {
                $subscriber->getGroups()->removeElement($group);
                $group->decrTotalCount();
            }
            $tr->persist($group);
        }

        foreach ($valueObject->getGroups() as $group) {
            if (!$subscriber->getGroups()->hasPivot($group)) {
                $subscriber->getGroups()->add($group);
                $group->incrTotalCount();
            }
            $tr->persist($group);
        }

        $tr->run();

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

        foreach ($subscriber->getGroups() as $groupPivot) {
            if ($group !== null && $group !== $groupPivot) {
                continue;
            }

            if ($subscriber->getGroups()->hasPivot($groupPivot)) {
                $subscriber->getGroups()->removeElement($groupPivot);
                $groupPivot->decrTotalCount();
            }
            $tr->persist($groupPivot);
        }

        if ($subscriber->getGroups()->count() > 0) {
            $tr->persist($subscriber);
        } else {
            $tr->delete($subscriber);
        }

        $tr->run();

        return true;
    }
}
