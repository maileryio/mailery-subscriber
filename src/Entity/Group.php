<?php

declare(strict_types=1);

namespace Mailery\Subscriber\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table;
use Cycle\Annotated\Annotation\Table\Index;
use Yiisoft\Auth\IdentityInterface;

/**
 * @Entity(
 *      table = "subscriber_groups",
 *      repository = "Mailery\Subscriber\Repository\GroupRepository",
 *      mapper = "Yiisoft\Yii\Cycle\Mapper\TimestampedMapper"
 * )
 * @Table(
 *      indexes = {
 *          @Index(columns = {"name"}, unique = true)
 *      }
 * )
 */
class Group implements IdentityInterface
{
    /**
     * @Column(type = "primary")
     * @var int|null
     */
    private $id;

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
}
