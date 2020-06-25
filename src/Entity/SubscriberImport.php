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
use Mailery\Storage\Entity\File;

/**
 * @Cycle\Annotated\Annotation\Entity(
 *      table = "subscriber_imports",
 *      repository = "Mailery\Subscriber\Repository\SubscriberImportRepository",
 *      mapper = "Mailery\Subscriber\Mapper\DefaultMapper"
 * )
 */
class SubscriberImport implements RoutableEntityInterface, LoggableEntityInterface
{
    use LoggableEntityTrait;

    /**
     * @Cycle\Annotated\Annotation\Column(type = "primary")
     * @var int|null
     */
    private $id;

    /**
     * @Cycle\Annotated\Annotation\Relation\BelongsTo(target = "Mailery\Brand\Entity\Brand", nullable = false)
     * @var Brand
     */
    private $brand;

    /**
     * @Cycle\Annotated\Annotation\Relation\ManyToMany(target = "Group", though = "SubscriberImportGroup", thoughInnerKey = "subscriber_import_id", nullable = false)
     * @var PivotedCollectionInterface
     */
    private $groups;

    /**
     * @Cycle\Annotated\Annotation\Relation\BelongsTo(target = "Mailery\Storage\Entity\File", nullable = true)
     * @var File
     */
    private $file;

    /**
     * @Cycle\Annotated\Annotation\Column(type = "json")
     * @var string
     */
    private $fieldsMap;

    /**
     * @Cycle\Annotated\Annotation\Column(type = "datetime")
     * @var \DateTimeImmutable
     */
    private $createdAt;

    public function __construct()
    {
        $this->groups = new PivotedCollection();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'Import #' . $this->getId();
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
     * @return array
     */
    public function getFieldsMap(): array
    {
        $fieldsMap = json_decode($this->fieldsMap, true);
        if (!is_array($fieldsMap)) {
            return [];
        }

        return $fieldsMap;
    }

    /**
     * @param array $fieldsMap
     * @return self
     */
    public function setFieldsMap(array $fieldsMap): self
    {
        $jsonString = json_encode($fieldsMap);
        if ($jsonString === false) {
            $jsonString = '[]';
        }

        $this->fieldsMap = $jsonString;

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
}
