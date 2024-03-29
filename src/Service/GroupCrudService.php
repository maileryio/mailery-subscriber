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

use Cycle\ORM\EntityManagerInterface;
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\ValueObject\GroupValueObject;
use Mailery\Brand\Entity\Brand;
use Yiisoft\Yii\Cycle\Data\Writer\EntityWriter;

class GroupCrudService
{
    /**
     * @var Brand
     */
    private Brand $brand;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private EntityManagerInterface $entityManager
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
     * @param GroupValueObject $valueObject
     * @return Group
     */
    public function create(GroupValueObject $valueObject): Group
    {
        $group = (new Group())
            ->setBrand($this->brand)
            ->setName($valueObject->getName())
        ;

        (new EntityWriter($this->entityManager))->write([$group]);

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
            ->setBrand($this->brand)
            ->setName($valueObject->getName())
        ;

        (new EntityWriter($this->entityManager))->write([$group]);

        return $group;
    }

    /**
     * @param Group $group
     * @return bool
     */
    public function delete(Group $group): bool
    {
        (new EntityWriter($this->entityManager))->delete([$group]);

        return true;
    }
}
