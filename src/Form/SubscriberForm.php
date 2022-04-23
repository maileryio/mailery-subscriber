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
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Yiisoft\Form\FormModel;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Email;
use Spiral\Database\Injection\Parameter;
use Yiisoft\Validator\Rule\InRange;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\RuleSet;

class SubscriberForm extends FormModel
{
    /**
     * @var string|null
     */
    private ?string $name = null;

    /**
     * @var string|null
     */
    private ?string $email = null;

    /**
     * @var int
     */
    private int $confirmed = 0;

    /**
     * @var array
     */
    private array $groups = [];

    /**
     * @var Subscriber|null
     */
    private ?Subscriber $subscriber = null;

    /**
     * @param GroupRepository $groupRepo
     * @param SubscriberRepository $subscriberRepo
     * @param BrandLocator $brandLocator
     */
    public function __construct(
        private GroupRepository $groupRepo,
        private SubscriberRepository $subscriberRepo,
        BrandLocator $brandLocator,
    ) {
        $this->groupRepo = $groupRepo->withBrand($brandLocator->getBrand());
        $this->subscriberRepo = $subscriberRepo->withBrand($brandLocator->getBrand());
        parent::__construct();
    }

    /**
     * @param Subscriber $subscriber
     * @return self
     */
    public function withEntity(Subscriber $subscriber): self
    {
        $new = clone $this;
        $new->subscriber = $subscriber;
        $new->name = $subscriber->getName();
        $new->email = $subscriber->getEmail();
        $new->confirmed = (int) $subscriber->getConfirmed();
        $new->groups = $subscriber->getGroups()->map(
            fn (Group $group) => $group->getId()
        )->toArray();

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function load(array $data, ?string $formName = null): bool
    {
        $scope = $formName ?? $this->getFormName();

        if (isset($data[$scope]['groups'])) {
            $data[$scope]['groups'] = array_filter((array) $data[$scope]['groups']);
        } else {
            $data[$scope]['groups'] = [];
        }

        return parent::load($data, $formName);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return bool
     */
    public function getConfirmed(): bool
    {
        return (bool) $this->confirmed;
    }

    /**
     * @return array
     */
    public function getGroups(): array
    {
        if (empty($this->groups)) {
            return [];
        }

        return $this->groupRepo->findAll([
            'id' => ['in' => new Parameter($this->groups, \PDO::PARAM_INT)],
        ]);
    }

    /**
     * @return array
     */
    public function getAttributeLabels(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'groups' => 'Add to groups',
            'confirmed' => 'Confirmed',
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
            ],
            'email' => [
                Required::rule(),
                Email::rule(),
                HasLength::rule()->max(255),
                Callback::rule(function ($value) {
                    $result = new Result();
                    $record = $this->subscriberRepo->findByEmail($value, $this->subscriber);

                    if ($record !== null) {
                        $result->addError('Subscriber with this email already exists.');
                    }

                    return $result;
                }),
            ],
            'confirmed' => [
                Required::rule(),
            ],
            'groups' => [
                Required::rule(),
                Each::rule(new RuleSet([
                    InRange::rule(array_keys($this->getGroupListOptions())),
                ]))->message('{error}'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function getGroupListOptions(): array
    {
        $options = [];
        $groups = $this->groupRepo->findAll();

        foreach ($groups as $group) {
            $options[$group->getId()] = $group->getName();
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getConfirmedListOptions(): array
    {
        return [
            0 => 'No',
            1 => 'Yes',
        ];
    }

}
