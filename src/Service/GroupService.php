<?php

namespace Mailery\Subscriber\Service;

use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\ValueObject\GroupValueObject;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Transaction;

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