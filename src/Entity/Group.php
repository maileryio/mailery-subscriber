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

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\Annotated\Annotation\Table;
use Cycle\Annotated\Annotation\Table\Index;
use Mailery\Brand\Entity\Brand;

/**
 * @Entity(
 *      table = "groups",
 *      repository = "Mailery\Subscriber\Repository\GroupRepository",
 *      mapper = "Yiisoft\Yii\Cycle\Mapper\TimestampedMapper"
 * )
 * @Table(
 *      indexes = {
 *          @Index(columns = {"name"}, unique = true)
 *      }
 * )
 */
class Group
{
    /**
     * @Column(type = "primary")
     * @var int|null
     */
    private $id;

    /**
     * @BelongsTo(target = "Mailery\Brand\Entity\Brand", nullable = false)
     * @var Brand
     */
    private $brand;

    /**
     * @Column(type = "string(32)")
     * @var string
     */
    private $name;

    /**
     * @Column(type = "integer", name = "total_count", default = 0)
     * @var string
     */
    private $totalCount = 0;

    /**
     * @Column(type = "integer", name = "bounced_count", default = 0)
     * @var string
     */
    private $bouncedCount = 0;

    /**
     * @Column(type = "integer", name = "complaint_count", default = 0)
     * @var string
     */
    private $complaintCount = 0;

    /**
     * @Column(type = "integer", name = "unconfirmed_count", default = 0)
     * @var string
     */
    private $unconfirmedCount = 0;

    /**
     * @Column(type = "integer", name = "unsubscribed_count", default = 0)
     * @var string
     */
    private $unsubscribedCount = 0;

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
     * @return int
     */
    public function getBouncedCount(): int
    {
        return $this->bouncedCount;
    }

    /**
     * @param int $bouncedCount
     * @return self
     */
    public function setBouncedCount(int $bouncedCount): self
    {
        $this->bouncedCount = $bouncedCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getComplaintCount(): int
    {
        return $this->complaintCount;
    }

    /**
     * @param int $complaintCount
     * @return self
     */
    public function setComplaintCount(int $complaintCount): self
    {
        $this->complaintCount = $complaintCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getUnconfirmedCount(): int
    {
        return $this->unconfirmedCount;
    }

    /**
     * @param int $unconfirmedCount
     * @return self
     */
    public function setUnconfirmedCount(int $unconfirmedCount): self
    {
        $this->unconfirmedCount = $unconfirmedCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getUnsubscribedCount(): int
    {
        return $this->unsubscribedCount;
    }

    /**
     * @param int $unsubscribedCount
     * @return self
     */
    public function setUnsubscribedCount(int $unsubscribedCount): self
    {
        $this->unsubscribedCount = $unsubscribedCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getActiveCount(): int
    {
        $activeCount = $this->getTotalCount()
            - $this->getBouncedCount()
            - $this->getComplaintCount()
            - $this->getUnconfirmedCount()
            - $this->getUnsubscribedCount();

        return $activeCount > 0 ? $activeCount : 0;
    }

    /**
     * @return self
     */
    public function incrTotalCount(): self
    {
        $this->totalCount++;

        return $this;
    }

    /**
     * @return self
     */
    public function decrTotalCount(): self
    {
        $this->totalCount--;

        return $this;
    }
}
