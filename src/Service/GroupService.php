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
use Mailery\Subscriber\ValueObject\GroupValueObject;

class GroupService
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
     * @param GroupValueObject $valueObject
     * @return Group
     */
    public function create(GroupValueObject $valueObject): Group
    {
        $group = (new Group())
            ->setName($valueObject->getName())
            ->setBrand($valueObject->getBrand())
        ;

        $tr = new Transaction($this->orm);
        $tr->persist($group);
        $tr->run();

        return $group;
    }

    /**
     * @param Group $group
     * @param GroupValueObject $valueObject
     * @return Group
     */
    public function update(Group $group, GroupValueObject $valueObject): Group
    {
        $group = $group
            ->setName($valueObject->getName())
            ->setBrand($valueObject->getBrand())
        ;

        $tr = new Transaction($this->orm);
        $tr->persist($group);
        $tr->run();

        return $group;
    }

    /**
     * @param Group $group
     * @return bool
     */
    public function delete(Group $group): bool
    {
        $tr = new Transaction($this->orm);
        $tr->delete($group);
        $tr->run();

        return true;
    }
}
