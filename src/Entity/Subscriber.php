<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Entity;

use Cycle\ORM\Collection\Pivoted\PivotedCollection;
use Mailery\Activity\Log\Entity\LoggableEntityInterface;
use Mailery\Activity\Log\Entity\LoggableEntityTrait;
use Mailery\Brand\Entity\Brand;
use Mailery\Common\Entity\RoutableEntityInterface;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Activity\Log\Mapper\LoggableMapper;
use Cycle\ORM\Collection\DoctrineCollectionFactory;
use Cycle\ORM\Entity\Behavior;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\ManyToMany;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

#[Entity(
    table: 'subscribers',
    repository: SubscriberRepository::class,
    mapper: LoggableMapper::class,
)]
#[Behavior\CreatedAt(
    field: 'createdAt',
    column: 'created_at',
)]
#[Behavior\UpdatedAt(
    field: 'updatedAt',
    column: 'updated_at',
)]
class Subscriber implements RoutableEntityInterface, LoggableEntityInterface
{
    use LoggableEntityTrait;

    #[Column(type: 'primary')]
    private int $id;

    #[Column(type: 'string(255)')]
    private string $name;

    #[Column(type: 'string(255)')]
    private string $email;

    #[BelongsTo(target: Brand::class)]
    private Brand $brand;

    #[ManyToMany(target: Group::class, though: SubscriberGroup::class, thoughInnerKey: 'subscriber_id', thoughOuterKey: 'subscriber_group_id', collection: DoctrineCollectionFactory::class)]
    private PivotedCollection $groups;

    #[Column(type: 'boolean')]
    private bool $confirmed = false;

    #[Column(type: 'boolean')]
    private bool $unsubscribed = false;

    #[Column(type: 'boolean')]
    private bool $bounced = false;

    #[Column(type: 'boolean')]
    private bool $complaint = false;

    #[Column(type: 'datetime')]
    private \DateTimeImmutable $createdAt;

    #[Column(type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->groups = new PivotedCollection();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Brand
     */
    public function getBrand(): Brand
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     * @return self
     */
    public function setBrand(Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return PivotedCollection
     */
    public function getGroups(): PivotedCollection
    {
        return $this->groups;
    }

    /**
     * @param PivotedCollection $groups
     * @return self
     */
    public function setGroups(PivotedCollection $groups): self
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * @return bool
     */
    public function getConfirmed(): bool
    {
        return $this->confirmed;
    }

    /**
     * @param bool $confirmed
     * @return self
     */
    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->getConfirmed();
    }

    /**
     * @return bool
     */
    public function getUnsubscribed(): bool
    {
        return $this->unsubscribed;
    }

    /**
     * @param bool $unsubscribed
     * @return self
     */
    public function setUnsubscribed(bool $unsubscribed): self
    {
        $this->unsubscribed = $unsubscribed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUnsubscribed(): bool
    {
        return $this->getUnsubscribed();
    }

    /**
     * @return bool
     */
    public function getBounced(): bool
    {
        return $this->bounced;
    }

    /**
     * @param bool $bounced
     * @return self
     */
    public function setBounced(bool $bounced): self
    {
        $this->bounced = $bounced;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBounced(): bool
    {
        return $this->getBounced();
    }

    /**
     * @return bool
     */
    public function getComplaint(): bool
    {
        return $this->complaint;
    }

    /**
     * @param bool $complaint
     * @return self
     */
    public function setComplaint(bool $complaint): self
    {
        $this->complaint = $complaint;

        return $this;
    }

    /**
     * @return bool
     */
    public function isComplaint(): bool
    {
        return $this->getComplaint();
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @inheritdoc
     */
    public function getIndexRouteName(): ?string
    {
        return '/user/subscriber/index';
    }

    /**
     * @inheritdoc
     */
    public function getIndexRouteParams(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getViewRouteName(): ?string
    {
        return '/subscriber/subscriber/view';
    }

    /**
     * @inheritdoc
     */
    public function getViewRouteParams(): array
    {
        return ['id' => $this->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getEditRouteName(): ?string
    {
        return '/subscriber/subscriber/edit';
    }

    /**
     * @inheritdoc
     */
    public function getEditRouteParams(): array
    {
        return ['id' => $this->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getDeleteRouteName(): ?string
    {
        return '/subscriber/subscriber/delete';
    }

    /**
     * @inheritdoc
     */
    public function getDeleteRouteParams(): array
    {
        return ['id' => $this->getId()];
    }
}
