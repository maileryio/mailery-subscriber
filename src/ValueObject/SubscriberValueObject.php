<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\ValueObject;

use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Form\SubscriberForm;
use Yiisoft\Validator\DataSetInterface;

class SubscriberValueObject implements DataSetInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $email;

    /**
     * @var bool
     */
    private bool $confirmed = false;

    /**
     * @var bool
     */
    private bool $unsubscribed = false;

    /**
     * @var bool
     */
    private bool $bounced = false;

    /**
     * @var bool
     */
    private bool $complaint = false;

    /**
     * @var Group[]
     */
    private array $groups;

    /**
     * @var Import|null
     */
    private ?Import $import = null;

    /**
     * @param SubscriberForm $form
     * @return self
     */
    public static function fromForm(SubscriberForm $form): self
    {
        $new = new self();
        $new->name = $form->getName();
        $new->email = $form->getEmail();
        $new->confirmed = $form->getConfirmed();
        $new->groups = $form->getGroups();

        return $new;
    }

    /**
     * @param array $array
     * @return self
     */
    public static function fromArray(array $array): self
    {
        $new = new self();
        $new->name = $array['name'] ?? '';
        $new->email = $array['email'] ?? '';

        return $new;
    }

    /**
     * @param Subscriber $subscriber
     * @return self
     */
    public static function fromEntity(Subscriber $subscriber): self
    {
        $new = new self();
        $new->name = $subscriber->getName();
        $new->email = $subscriber->getEmail();
        $new->confirmed = $subscriber->getConfirmed();
        $new->unsubscribed = $subscriber->getUnsubscribed();
        $new->bounced = $subscriber->getBounced();
        $new->complaint = $subscriber->getComplaint();
        $new->groups = $subscriber->getGroups()->toArray();

        return $new;
    }

    /**
     * @param Group[] $groups
     * @return self
     */
    public function withGroups(array $groups): self
    {
        $new = clone $this;
        $new->groups = $groups;

        return $new;
    }

    /**
     * @param Import $import
     * @return self
     */
    public function withImport(Import $import): self
    {
        $new = clone $this;
        $new->import = $import;

        return $new;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return bool
     */
    public function getConfirmed(): bool
    {
        return $this->confirmed;
    }

    /**
     * @return bool
     */
    public function getUnsubscribed(): bool
    {
        return $this->unsubscribed;
    }

    /**
     * @return bool
     */
    public function getBounced(): bool
    {
        return $this->bounced;
    }

    /**
     * @return bool
     */
    public function getComplaint(): bool
    {
        return $this->complaint;
    }

    /**
     * @return Group[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @return Import|null
     */
    public function getImport(): ?Import
    {
        return $this->import;
    }

    /**
     * @return self
     */
    public function asConfirmed(): self
    {
        $new = clone $this;
        $new->confirmed = true;

        return $new;
    }

    /**
     * @param bool $value
     * @return self
     */
    public function asUnsubscribed(bool $value = true): self
    {
        $new = clone $this;
        $new->unsubscribed = $value;

        return $new;
    }

    /**
     * @return self
     */
    public function asBounced(): self
    {
        $new = clone $this;
        $new->bounced = true;

        return $new;
    }

    /**
     * @return self
     */
    public function asComplaint(): self
    {
        $new = clone $this;
        $new->complaint = true;

        return $new;
    }

    /**
     * @param string $attribute
     * @return bool
     */
    public function hasAttribute(string $attribute): bool
    {
        return isset($this->$attribute);
    }

    /**
     * @param string $attribute
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function getAttributeValue(string $attribute)
    {
        if (!isset($this->$attribute)) {
            throw new \InvalidArgumentException("There is no \"$attribute\" in object value.");
        }

        return $this->$attribute;
    }
}
