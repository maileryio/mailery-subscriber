<?php

declare(strict_types=1);

namespace Mailery\Subscriber\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\Annotated\Annotation\Relation\ManyToMany;
use Cycle\ORM\Relation\Pivoted\PivotedCollection;
use Cycle\ORM\Relation\Pivoted\PivotedCollectionInterface;
use Mailery\Brand\Entity\Brand;

/**
 * @Entity(
 *      table = "subscribers",
 *      repository = "Mailery\Subscriber\Repository\SubscriberRepository",
 *      mapper = "Yiisoft\Yii\Cycle\Mapper\TimestampedMapper"
 * )
 * @Table(
 *      indexes = {
 *          @Index(columns = {"email"}, unique = true)
 *      }
 * )
 */
class Subscriber
{
    /**
     * @Column(type = "primary")
     * @var int|null
     */
    private $id;

    /**
     * @Column(type = "string(255)")
     * @var string
     */
    private $name;

    /**
     * @Column(type = "string(255)")
     * @var string
     */
    private $email;

    /**
     * @BelongsTo(target = "Mailery\Brand\Entity\Brand", nullable = false)
     * @var Brand
     */
    private $brand;

    /**
     * @ManyToMany(target = "Group", though = "SubscriberGroup", nullable = false)
     * @var PivotedCollectionInterface
     */
    private $groups;

    /**
     * @Column(type = "boolean", default = false)
     * @var string
     */
    private $confirmed = false;

    /**
     * @Column(type = "boolean", default = false)
     * @var string
     */
    private $unsubscribed = false;

    /**
     * @Column(type = "boolean", default = false)
     * @var string
     */
    private $bounced = false;

    /**
     * @Column(type = "boolean", default = false)
     * @var string
     */
    private $complaint = false;

    public function __construct()
    {
        $this->groups = new PivotedCollection();
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
}
