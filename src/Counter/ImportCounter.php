<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Counter;

use Mailery\Subscriber\Entity\Import;

class ImportCounter
{

    /**
     * @var Import|null
     */
    private ?Import $import = null;

    /**
     * @param Import $import
     * @return self
     */
    public function withImport(Import $import): self
    {
        $new = clone $this;
        $new->import = $import;

        return $new;
    }

    /**
     * @return int
     */
    public function getProcessedCount(): int
    {
        return $this->import->getCreatedCount()
            + $this->import->getUpdatedCount()
            + $this->import->getSkippedCount();
    }

    /**
     * @return int
     */
    public function getCreatedCount(): int
    {
        return $this->import->getCreatedCount();
    }

    /**
     * @return int
     */
    public function getUpdatedCount(): int
    {
        return $this->import->getUpdatedCount();
    }

    /**
     * @return int
     */
    public function getSkippedCount(): int
    {
        return $this->import->getSkippedCount();
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->import->getTotalCount();
    }

}
