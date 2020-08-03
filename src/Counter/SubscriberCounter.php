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
use Mailery\Subscriber\Entity\Group;

class SubscriberCounter
{
    private const TOTAL_COUNT_TPL = 'counter:subscriber:brand-{brandId}:group-{groupId}:total';
    private const CONFIRMED_COUNT_TPL = 'counter:subscriber:brand-{brandId}:group-{groupId}:confirmed';
    private const UNSUBSCRIBED_COUNT_TPL = 'counter:subscriber:brand-{brandId}:group-{groupId}:unsubscribed';
    private const BOUNCED_COUNT_TPL = 'counter:subscriber:brand-{brandId}:group-{groupId}:bounced';
    private const COMPLAINT_COUNT_TPL = 'counter:subscriber:brand-{brandId}:group-{groupId}:complaint';

    /**
     * @var Brand|null
     */
    private ?Brand $brand = null;

    /**
     * @var Group|null
     */
    private ?Group $group = null;

    /**
     * @var CounterFactory
     */
    private CounterFactory $counterFactory;

    /**
     * @var array
     */
    private array $totalCounter = [];

    /**
     * @var array
     */
    private array $confirmedCounter = [];

    /**
     * @var array
     */
    private array $unsubscribedCounter = [];

    /**
     * @var array
     */
    private array $bouncedCounter = [];

    /**
     * @var array
     */
    private array $complaintCounter = [];

    /**
     * @param CounterFactory $counterFactory
     */
    public function __construct(CounterFactory $counterFactory)
    {
        $this->counterFactory = $counterFactory;
    }

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
     * @param Group $group
     * @return self
     */
    public function withGroup(Group $group): self
    {
        $new = clone $this;
        $new->group = $group;
        $new->brand = $group->getBrand();

        return $new;
    }

    /**
     * @return int
     */
    public function getActiveCount(): int
    {
        $activeCount = $this->getConfirmedCount()
            - $this->getUnsubscribedCount()
            - $this->getBouncedCount()
            - $this->getComplaintCount();

        return max(0, $activeCount);
    }

    /**
     * @return int
     */
    public function getUnconfirmedCount(): int
    {
        $activeCount = $this->getTotalCount() - $this->getConfirmedCount();

        return max(0, $activeCount);
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->getTotalCounter()->get();
    }

    /**
     * @param int $amount
     * @return bool
     */
    public function setTotalCount(int $amount = 0): bool
    {
        return $this->getTotalCounter()->set($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function incrTotalCount(int $amount = 1): int
    {
        return $this->getTotalCounter()->incr($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function decrTotalCount(int $amount = 1): int
    {
        return $this->getTotalCounter()->decr($amount);
    }

    /**
     * @return int
     */
    public function getConfirmedCount(): int
    {
        return $this->getConfirmedCounter()->get();
    }

    /**
     * @param int $amount
     * @return bool
     */
    public function setConfirmedCount(int $amount = 0): bool
    {
        return $this->getConfirmedCounter()->set($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function incrConfirmedCount(int $amount = 1): int
    {
        return $this->getConfirmedCounter()->incr($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function decrConfirmedCount(int $amount = 1): int
    {
        return $this->getConfirmedCounter()->decr($amount);
    }

    /**
     * @return int
     */
    public function getUnsubscribedCount(): int
    {
        return $this->getUnsubscribedCounter()->get();
    }

    /**
     * @param int $amount
     * @return bool
     */
    public function setUnsubscribedCount(int $amount = 0): bool
    {
        return $this->getUnsubscribedCounter()->set($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function incrUnsubscribedCount(int $amount = 1): int
    {
        return $this->getUnsubscribedCounter()->incr($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function decrUnsubscribedCount(int $amount = 1): int
    {
        return $this->getUnsubscribedCounter()->decr($amount);
    }

    /**
     * @return int
     */
    public function getBouncedCount(): int
    {
        return $this->getBouncedCounter()->get();
    }

    /**
     * @param int $amount
     * @return bool
     */
    public function setBouncedCount(int $amount = 0): bool
    {
        return $this->getBouncedCounter()->set($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function incrBouncedCount(int $amount = 1): int
    {
        return $this->getBouncedCounter()->incr($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function decrBouncedCount(int $amount = 1): int
    {
        return $this->getBouncedCounter()->decr($amount);
    }

    /**
     * @return int
     */
    public function getComplaintCount(): int
    {
        return $this->getComplaintCounter()->get();
    }

    /**
     * @param int $amount
     * @return bool
     */
    public function setComplaintCount(int $amount = 0): bool
    {
        return $this->getComplaintCounter()->set($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function incrComplaintCount(int $amount = 1): int
    {
        return $this->getComplaintCounter()->incr($amount);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function decrComplaintCount(int $amount = 1): int
    {
        return $this->getComplaintCounter()->decr($amount);
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
                '{groupId}' => $this->group ? $this->group->getId() : '0',
            ]
        );
    }

    /**
     * @return Counter
     */
    private function getTotalCounter(): Counter
    {
        $key = $this->getKey(self::TOTAL_COUNT_TPL);

        if (!isset($this->totalCounter[$key])) {
            $this->totalCounter[$key] = $this->counterFactory->getCounter($key);
        }

        return $this->totalCounter[$key];
    }

    /**
     * @return Counter
     */
    private function getConfirmedCounter(): Counter
    {
        $key = $this->getKey(self::CONFIRMED_COUNT_TPL);

        if (!isset($this->confirmedCounter[$key])) {
            $this->confirmedCounter[$key] = $this->counterFactory->getCounter($key);
        }

        return $this->confirmedCounter[$key];
    }

    /**
     * @return Counter
     */
    private function getUnsubscribedCounter(): Counter
    {
        $key = $this->getKey(self::UNSUBSCRIBED_COUNT_TPL);

        if (!isset($this->unsubscribedCounter[$key])) {
            $this->unsubscribedCounter[$key] = $this->counterFactory->getCounter($key);
        }

        return $this->unsubscribedCounter[$key];
    }

    /**
     * @return Counter
     */
    private function getBouncedCounter(): Counter
    {
        $key = $this->getKey(self::BOUNCED_COUNT_TPL);

        if (!isset($this->bouncedCounter[$key])) {
            $this->bouncedCounter[$key] = $this->counterFactory->getCounter($key);
        }

        return $this->bouncedCounter[$key];
    }

    /**
     * @return Counter
     */
    private function getComplaintCounter(): Counter
    {
        $key = $this->getKey(self::COMPLAINT_COUNT_TPL);

        if (!isset($this->complaintCounter[$key])) {
            $this->complaintCounter[$key] = $this->counterFactory->getCounter($key);
        }

        return $this->complaintCounter[$key];
    }
}
