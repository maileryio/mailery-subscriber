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

use Cycle\ORM\Relation\Pivoted\PivotedCollection;
use Cycle\ORM\Relation\Pivoted\PivotedCollectionInterface;
use Mailery\Activity\Log\Entity\LoggableEntityInterface;
use Mailery\Activity\Log\Entity\LoggableEntityTrait;
use Mailery\Brand\Entity\Brand;
use Mailery\Common\Entity\RoutableEntityInterface;

/**
 * @Cycle\Annotated\Annotation\Entity(
 *      table = "subscribers",
 *      repository = "Mailery\Subscriber\Repository\SubscriberRepository",
 *      mapper = "Mailery\Subscriber\Mapper\DefaultMapper"
 * )
 */
class Subscriber implements RoutableEntityInterface, LoggableEntityInterface
{
    use LoggableEntityTrait;

    /**
     * @Cycle\Annotated\Annotation\Column(type = "primary")
     * @var int|null
     */
    private $id;

    /**
     * @Cycle\Annotated\Annotation\Column(type = "string(255)")
     * @var string
     */
    private $name;

    /**
     * @Cycle\Annotated\Annotation\Column(type = "string(255)")
     * @var string
     */
    private $email;

    /**
     * @Cycle\Annotated\Annotation\Relation\BelongsTo(target = "Mailery\Brand\Entity\Brand", nullable = false)
     * @var Brand
     */
    private $brand;

    /**
     * @Cycle\Annotated\Annotation\Relation\ManyToMany(target = "Mailery\Subscriber\Entity\Group", though = "Mailery\Subscriber\Entity\SubscriberGroup", nullable = false)
     * @var PivotedCollectionInterface
     */
    private $groups;

    /**
     * @Cycle\Annotated\Annotation\Column(type = "boolean", default = false)
     * @var bool
     */
    private $confirmed = false;

    /**
     * @Cycle\Annotated\Annotation\Column(type = "boolean", default = false)
     * @var bool
     */
    private $unsubscribed = false;

    /**
     * @Cycle\Annotated\Annotation\Column(type = "boolean", default = false)
     * @var bool
     */
    private $bounced = false;

    /**
     * @Cycle\Annotated\Annotation\Column(type = "boolean", default = false)
     * @var bool
     */
    private $complaint = false;

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
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id ? (string) $this->id : null;
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
     * @return PivotedCollectionInterface
     */
    public function getGroups(): PivotedCollectionInterface
    {
        return $this->groups;
    }

    /**
     * @param PivotedCollectionInterface $groups
     * @return self
     */
    public function setGroups(PivotedCollectionInterface $groups): self
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
     * {@inheritdoc}
     */
    public function getEditRouteName(): ?string
    {
        return '/subscriber/subscriber/edit';
    }

    /**
     * {@inheritdoc}
     */
    public function getEditRouteParams(): array
    {
        return ['id' => $this->getId()];
    }

    /**
     * {@inheritdoc}
     */
    public function getViewRouteName(): ?string
    {
        return '/subscriber/subscriber/view';
    }

    /**
     * {@inheritdoc}
     */
    public function getViewRouteParams(): array
    {
        return ['id' => $this->getId()];
    }
}
