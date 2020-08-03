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
use Mailery\Subscriber\Entity\Import;

/**
 * @Cycle\Annotated\Annotation\Entity(
 *      table = "subscriber_import_errors",
 *      repository = "Mailery\Subscriber\Repository\ImportErrorRepository",
 *      mapper = "Mailery\Subscriber\Mapper\DefaultMapper"
 * )
 */
class ImportError implements LoggableEntityInterface
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
    private $value;

    /**
     * @Cycle\Annotated\Annotation\Column(type = "string(255)")
     * @var string
     */
    private $error;

    /**
     * @Cycle\Annotated\Annotation\Relation\BelongsTo(target = "Mailery\Subscriber\Entity\Import", innerKey = "subscriber_import_id", nullable = false)
     * @var Import
     */
    private $import;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'Import error #' . $this->getId();
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
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param string $error
     * @return self
     */
    public function setError(string $error): self
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return Import
     */
    public function getImport(): Import
    {
        return $this->import;
    }

    /**
     * @param Import $import
     * @return self
     */
    public function setImport(Import $import): self
    {
        $this->import = $import;

        return $this;
    }
}
