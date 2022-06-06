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

use Mailery\Activity\Log\Entity\LoggableEntityInterface;
use Mailery\Activity\Log\Entity\LoggableEntityTrait;
use Mailery\Brand\Entity\Brand;
use Mailery\Common\Entity\RoutableEntityInterface;
use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Activity\Log\Mapper\LoggableMapper;
use Cycle\ORM\Entity\Behavior;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

#[Entity(
    table: 'subscriber_groups',
    repository: GroupRepository::class,
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
class Group implements RoutableEntityInterface, LoggableEntityInterface
{
    use LoggableEntityTrait;

    #[Column(type: 'primary')]
    private int $id;

    #[BelongsTo(target: Brand::class)]
    private Brand $brand;

    #[Column(type: 'string(255)')]
    private string $name;

    #[Column(type: 'datetime')]
    private \DateTimeImmutable $createdAt;

    #[Column(type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

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
        return '/user/default/index';
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
        return '/subscriber/group/view';
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
        return '/subscriber/group/edit';
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
        return '/subscriber/group/delete';
    }

    /**
     * @inheritdoc
     */
    public function getDeleteRouteParams(): array
    {
        return ['id' => $this->getId()];
    }
}
