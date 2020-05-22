<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Form;

use Cycle\ORM\ORMInterface;
use FormManager\Factory as F;
use FormManager\Form;
use Mailery\Brand\Entity\Brand;
use Mailery\Brand\Service\BrandLocatorInterface as BrandLocator;
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Subscriber\Service\GroupService;
use Mailery\Subscriber\ValueObject\GroupValueObject;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class GroupForm extends Form
{
    /**
     * @var Brand
     */
    private Brand $brand;

    /**
     * @var ORMInterface
     */
    private ORMInterface $orm;

    /**
     * @var Group|null
     */
    private ?Group $group;

    /**
     * @var GroupService
     */
    private $groupService;

    /**
     * @param BrandLocator $brandLocator
     * @param GroupService $groupService
     * @param ORMInterface $orm
     */
    public function __construct(BrandLocator $brandLocator, GroupService $groupService, ORMInterface $orm)
    {
        $this->orm = $orm;
        $this->brand = $brandLocator->getBrand();
        $this->groupService = $groupService;
        parent::__construct($this->inputs());
    }

    /**
     * @param Group $group
     * @return self
     */
    public function withGroup(Group $group): self
    {
        $this->group = $group;
        $this->offsetSet('', F::submit('Update'));

        $this['name']->setValue($group->getName());

        return $this;
    }

    /**
     * @return Group|null
     */
    public function save(): ?Group
    {
        if (!$this->isValid()) {
            return null;
        }

        $valueObject = GroupValueObject::fromForm($this)
            ->withBrand($this->brand);

        if (($group = $this->group) === null) {
            $group = $this->groupService->create($valueObject);
        } else {
            $this->groupService->update($group, $valueObject);
        }

        return $group;
    }

    /**
     * @return array
     */
    private function inputs(): array
    {
        $nameConstraint = new Constraints\Callback([
            'callback' => function ($value, ExecutionContextInterface $context) {
                if (empty($value)) {
                    return;
                }

                $group = $this->getGroupRepository()->findByName($value, $this->group);
                if ($group !== null) {
                    $context->buildViolation('Group with this name already exists.')
                        ->atPath('name')
                        ->addViolation();
                }
            },
        ]);

        return [
            'name' => F::text('Name')
                ->addConstraint(new Constraints\NotBlank())
                ->addConstraint(new Constraints\Length([
                    'min' => 4,
                ]))
                ->addConstraint($nameConstraint),

            '' => F::submit($this->group === null ? 'Create' : 'Update'),
        ];
    }

    /**
     * @return GroupRepository
     */
    private function getGroupRepository(): GroupRepository
    {
        return $this->orm->getRepository(Group::class)
            ->withBrand($this->brand);
    }
}
