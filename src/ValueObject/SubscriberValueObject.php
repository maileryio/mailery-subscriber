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

use Mailery\Brand\Contract\BrandInterface as Brand;
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Form\SubscriberForm;

class SubscriberValueObject
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
    private bool $confirmed;

    /**
     * @var bool
     */
    private bool $unsubscribed;

    /**
     * @var bool
     */
    private bool $bounced;

    /**
     * @var bool
     */
    private bool $complaint;

    /**
     * @var Brand
     */
    private Brand $brand;

    /**
     * @var Group[]
     */
    private array $groups;

    /**
     * @param SubscriberForm $form
     * @return self
     */
    public static function fromForm(SubscriberForm $form): self
    {
        $new = new self();

        $new->name = $form['name']->getValue();
        $new->email = $form['email']->getValue();
        $new->confirmed = filter_var($form['confirmed']->getValue(), FILTER_VALIDATE_BOOLEAN);
        $new->unsubscribed = false;
        $new->bounced = false;
        $new->complaint = false;

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
     * @return Brand
     */
    public function getBrand(): Brand
    {
        return $this->brand;
    }

    /**
     * @return array
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

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
     * @param Group[] $groups
     * @return self
     */
    public function withGroups(array $groups): self
    {
        $new = clone $this;
        $new->groups = $groups;

        return $new;
    }
}
