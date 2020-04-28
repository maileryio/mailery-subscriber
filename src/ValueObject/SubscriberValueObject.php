<?php

namespace Mailery\Subscriber\ValueObject;

use Mailery\Subscriber\Form\SubscriberForm;
use Mailery\Brand\Service\BrandInterface;
use Mailery\Subscriber\Entity\Group;

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
     * @var BrandInterface
     */
    private BrandInterface $brand;

    /**
     * @var Group[]
     */
    private array $groups;

    /**
     * @param SubscriberForm $form
     * @return \self
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
     * @return BrandInterface
     */
    public function getBrand(): BrandInterface
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
     * @param BrandInterface $brand
     * @return self
     */
    public function withBrand(BrandInterface $brand): self
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
