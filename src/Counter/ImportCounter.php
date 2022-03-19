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

use Mailery\Brand\Entity\Brand;
use Mailery\Common\Counter\Counter;
use Mailery\Common\Counter\CounterFactory;
use Mailery\Subscriber\Entity\Import;

class ImportCounter
{
    private const INSERTED_COUNT_TPL = 'counter:subscriber:brand-{brandId}:import-{importId}:inserted';
    private const UPDATED_COUNT_TPL = 'counter:subscriber:brand-{brandId}:import-{importId}:updated';
    private const SKIPPED_COUNT_TPL = 'counter:subscriber:brand-{brandId}:import-{importId}:skipped';

    /**
     * @var Brand|null
     */
    private ?Brand $brand = null;

    /**
     * @var Import|null
     */
    private ?Import $import = null;

    /**
     * @var array
     */
    private array $insertedCounter = [];

    /**
     * @var array
     */
    private array $updatedCounter = [];

    /**
     * @var array
     */
    private array $skippedCounter = [];

    /**
     * @param CounterFactory $counterFactory
     */
    public function __construct(
        private CounterFactory $counterFactory
    ) {}

    /**
     * @param Brand $brand
     * @return self
     */
    public function withBrand(Brand $brand): self
    {
        $new = clone $this;
        $new->brand = $brand;

        return $new;
    }

    /**
     * @param Import $import
     * @return self
     */
    public function withImport(Import $import): self
    {
        $new = clone $this;
        $new->import = $import;
        $new->brand = $import->getBrand();

        return $new;
    }

    /**
     * @return int
     */
    public function getProcessedCount(): int
    {
        return $this->getInsertedCount() + $this->getUpdatedCount() + $this->getSkippedCount();
    }

    /**
     * @return int
     */
    public function getInsertedCount(): int
    {
        return $this->getInsertedCounter()->get();
    }

    /**
     * @param int $amount
     * @return bool
     */
    public function setInsertedCount(int $amount = 0): bool
    {
        return $this->getInsertedCounter()->set($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function incrInsertedCount(int $amount = 1): int
    {
        return $this->getInsertedCounter()->incr($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function decrInsertedCount(int $amount = 1): int
    {
        return $this->getInsertedCounter()->decr($amount);
    }

    /**
     * @return int
     */
    public function getUpdatedCount(): int
    {
        return $this->getUpdatedCounter()->get();
    }

    /**
     * @param int $amount
     * @return bool
     */
    public function setUpdatedCount(int $amount = 0): bool
    {
        return $this->getUpdatedCounter()->set($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function incrUpdatedCount(int $amount = 1): int
    {
        return $this->getUpdatedCounter()->incr($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function decrUpdatedCount(int $amount = 1): int
    {
        return $this->getUpdatedCounter()->decr($amount);
    }

    /**
     * @return int
     */
    public function getSkippedCount(): int
    {
        return $this->getSkippedCounter()->get();
    }

    /**
     * @param int $amount
     * @return bool
     */
    public function setSkippedCount(int $amount = 0): bool
    {
        return $this->getSkippedCounter()->set($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function incrSkippedCount(int $amount = 1): int
    {
        return $this->getSkippedCounter()->incr($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function decrSkippedCount(int $amount = 1): int
    {
        return $this->getSkippedCounter()->decr($amount);
    }

    /**
     * @param string $template
     * @return string
     */
    private function getKey(string $template): string
    {
        if ($this->brand === null) {
            throw new \RuntimeException('Brand required');
        }

        return strtr(
            $template,
            [
                '{brandId}' => $this->brand->getId(),
                '{importId}' => $this->import ? $this->import->getId() : '0',
            ]
        );
    }

    /**
     * @return Counter
     */
    private function getInsertedCounter(): Counter
    {
        $key = $this->getKey(self::INSERTED_COUNT_TPL);

        if (!isset($this->insertedCounter[$key])) {
            $this->insertedCounter[$key] = $this->counterFactory->getCounter($key);
        }

        return $this->insertedCounter[$key];
    }

    /**
     * @return Counter
     */
    private function getUpdatedCounter(): Counter
    {
        $key = $this->getKey(self::UPDATED_COUNT_TPL);

        if (!isset($this->updatedCounter[$key])) {
            $this->updatedCounter[$key] = $this->counterFactory->getCounter($key);
        }

        return $this->updatedCounter[$key];
    }

    /**
     * @return Counter
     */
    private function getSkippedCounter(): Counter
    {
        $key = $this->getKey(self::SKIPPED_COUNT_TPL);

        if (!isset($this->skippedCounter[$key])) {
            $this->skippedCounter[$key] = $this->counterFactory->getCounter($key);
        }

        return $this->skippedCounter[$key];
    }
}
