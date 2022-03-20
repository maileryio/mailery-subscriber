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
use Mailery\Storage\Entity\File;
use Mailery\Subscriber\Repository\ImportRepository;
use Mailery\Subscriber\Field\ImportStatus;
use Mailery\Activity\Log\Mapper\LoggableMapper;
use Cycle\ORM\Collection\DoctrineCollectionFactory;
use Cycle\ORM\Entity\Behavior;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\ManyToMany;
use Cycle\Annotated\Annotation\Relation\HasMany;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

#[Entity(
    table: 'subscriber_imports',
    repository: ImportRepository::class,
    mapper: LoggableMapper::class
)]
#[Behavior\CreatedAt(
    field: 'createdAt',
    column: 'created_at',
)]
#[Behavior\UpdatedAt(
    field: 'updatedAt',
    column: 'updated_at',
)]
class Import implements RoutableEntityInterface, LoggableEntityInterface
{
    use LoggableEntityTrait;

    #[Column(type: 'primary')]
    private int $id;

    #[BelongsTo(target: Brand::class)]
    private Brand $brand;

    #[ManyToMany(target: Group::class, though: ImportGroup::class, thoughInnerKey: 'subscriber_import_id', thoughOuterKey: 'subscriber_group_id', collection: DoctrineCollectionFactory::class)]
    private PivotedCollection $groups;

    #[HasMany(target: ImportError::class, outerKey: 'subscriber_import_id', collection: DoctrineCollectionFactory::class)]
    private PivotedCollection $errors;

    #[BelongsTo(target: File::class)]
    private File $file;

    #[Column(type: 'enum(pending, running, paused, errored, completed)', typecast: ImportStatus::class)]
    private ImportStatus $status;

    #[Column(type: 'integer')]
    private int $totalCount;

    #[Column(type: 'json')]
    private string $fields;

    #[Column(type: 'datetime')]
    private \DateTimeImmutable $createdAt;

    #[Column(type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->groups = new PivotedCollection();
        $this->errors = new PivotedCollection();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'Import #' . $this->getObjectId();
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
     * @return PivotedCollection
     */
    public function getErrors(): PivotedCollection
    {
        return $this->errors;
    }

    /**
     * @param PivotedCollection $errors
     * @return self
     */
    public function setErrors(PivotedCollection $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * @param File $file
     * @return self
     */
    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return ImportStatus
     */
    public function getStatus(): ImportStatus
    {
        return $this->status;
    }

    /**
     * @param ImportStatus $status
     * @return self
     */
    public function setStatus(ImportStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     * @return self
     */
    public function setTotalCount(int $totalCount): self
    {
        $this->totalCount = $totalCount;

        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        $fields = json_decode($this->fields, true);
        if (!is_array($fields)) {
            return [];
        }

        return $fields;
    }

    /**
     * @param array $fields
     * @return self
     */
    public function setFields(array $fields): self
    {
        $jsonString = json_encode($fields);
        if ($jsonString === false) {
            $jsonString = '[]';
        }

        $this->fields = $jsonString;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @inheritdoc
     */
    public function getIndexRouteName(): ?string
    {
        return '/subscriber/import/index';
    }

    /**
     * @inheritdoc
     */
    public function getIndexRouteParams(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getViewRouteName(): ?string
    {
        return '/subscriber/import/view';
    }

    /**
     * {@inheritdoc}
     */
    public function getViewRouteParams(): array
    {
        return ['id' => $this->getId()];
    }

    /**
     * {@inheritdoc}
     */
    public function getEditRouteName(): ?string
    {
        return '/subscriber/import/edit';
    }

    /**
     * {@inheritdoc}
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
        throw new \RuntimeException('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function getDeleteRouteParams(): array
    {
        throw new \RuntimeException('Not implemented');
    }
}
