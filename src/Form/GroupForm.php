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

use Mailery\Brand\BrandLocatorInterface as BrandLocator;
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Repository\GroupRepository;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Result;
use Yiisoft\Form\FormModel;

class GroupForm extends FormModel
{

    /**
     * @var string|null
     */
    private ?string $name = null;

    /**
     * @var Group|null
     */
    private ?Group $group = null;

    /**
     * @var GroupRepository
     */
    private GroupRepository $groupRepo;

    /**
     * @param BrandLocator $brandLocator
     * @param GroupRepository $groupRepo
     */
    public function __construct(
        BrandLocator $brandLocator,
        GroupRepository $groupRepo
    ) {
        $this->groupRepo = $groupRepo->withBrand($brandLocator->getBrand());

        parent::__construct();
    }

    /**
     * @param Group $group
     * @return self
     */
    public function withEntity(Group $group): self
    {
        $new = clone $this;
        $new->group = $group;
        $new->name = $group->getName();

        return $new;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getAttributeLabels(): array
    {
        return [
            'name' => 'Name',
        ];
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        return [
            'name' => [
                Required::rule(),
                HasLength::rule()->min(3)->max(255),
                Callback::rule(function ($value) {
                    $result = new Result();
                    $record = $this->groupRepo->findByName($value, $this->group);

                    if ($record !== null) {
                        $result->addError('Group with this name already exists.');
                    }

                    return $result;
                }),
            ],
        ];
    }

}
