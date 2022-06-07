<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\ValueObject;

use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Field\ImportStatus;
use Mailery\Subscriber\Form\ImportForm;
use HttpSoft\Message\UploadedFile;

class ImportValueObject
{
    /**
     * @var UploadedFile
     */
    private UploadedFile $file;

    /**
     * @var Group[]
     */
    private array $groups;

    /**
     * @var array
     */
    private array $fields;

    /**
     * @var ImportStatus
     */
    private ImportStatus $status;

    /**
     * @var int
     */
    private int $createdCount;

    /**
     * @var int
     */
    private int $updatedCount;

    /**
     * @var int
     */
    private int $skippedCount;

    /**
     * @param ImportForm $form
     * @return self
     */
    public static function fromForm(ImportForm $form): self
    {
        $new = new self();
        $new->file = $form->getFile();
        $new->groups = $form->getGroups();
        $new->fields = $form->getFields();

        return $new;
    }

    /**
     * @param Import $entity
     * @return self
     */
    public static function fromEntity(Import $entity): self
    {
        $new = new self();
        $new->status = $entity->getStatus();
        $new->createdCount = $entity->getCreatedCount();
        $new->updatedCount = $entity->getUpdatedCount();
        $new->skippedCount = $entity->getSkippedCount();

        return $new;
    }

    /**
     * @return UploadedFile
     */
    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    /**
     * @return array
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return ImportStatus
     */
    public function getStatus(): ImportStatus
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    /**
     * @return int
     */
    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    /**
     * @return int
     */
    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    /**
     * @return self
     */
    public function incrCreatedCount(): self
    {
        $new = clone $this;
        $new->createdCount++;

        return $new;
    }

    /**
     * @return self
     */
    public function incrUpdatedCount(): self
    {
        $new = clone $this;
        $new->updatedCount++;

        return $new;
    }

    /**
     * @return self
     */
    public function incrSkippedCount(): self
    {
        $new = clone $this;
        $new->skippedCount++;

        return $new;
    }

    /**
     * @return self
     */
    public function asPending(): self
    {
        $new = clone $this;
        $new->status = ImportStatus::asPending();

        return $new;
    }

    /**
     * @return self
     */
    public function asRunning(): self
    {
        $new = clone $this;
        $new->status = ImportStatus::asRunning();

        return $new;
    }

    /**
     * @return self
     */
    public function asErrored(): self
    {
        $new = clone $this;
        $new->status = ImportStatus::asErrored();

        return $new;
    }

    /**
     * @return self
     */
    public function asCompleted(): self
    {
        $new = clone $this;
        $new->status = ImportStatus::asCompleted();

        return $new;
    }

}
