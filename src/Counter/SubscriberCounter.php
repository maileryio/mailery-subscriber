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
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Repository\SubscriberRepository;

class SubscriberCounter
{

    /**
     * @param SubscriberRepository $repo
     */
    public function __construct(
        private SubscriberRepository $repo
    ) {}

    /**
     * @param Brand $brand
     * @return self
     */
    public function withBrand(Brand $brand): self
    {
        $new = clone $this;
        $new->repo = $new->repo->withBrand($brand);

        return $new;
    }

    /**
     * @param Group $group
     * @return self
     */
    public function withGroup(Group $group): self
    {
        $new = clone $this;
        $new->repo = $new->repo->withGroup($group);

        return $new;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->repo
            ->select()
            ->count();
    }

    /**
     * @return int
     */
    public function getActiveCount(): int
    {
        return $this->repo
            ->asConfirmed(true)
            ->asUnsubscribed(false)
            ->asBounced(false)
            ->asComplaint(false)
            ->select()
            ->count();
    }

    /**
     * @return int
     */
    public function getUnconfirmedCount(): int
    {
        return $this->repo
            ->asConfirmed(false)
            ->select()
            ->count();
    }

    /**
     * @return int
     */
    public function getConfirmedCount(): int
    {
        return $this->repo
            ->asConfirmed(true)
            ->select()
            ->count();
    }

    /**
     * @return int
     */
    public function getUnsubscribedCount(): int
    {
        return $this->repo
            ->asUnsubscribed(true)
            ->select()
            ->count();
    }

    /**
     * @return int
     */
    public function getBouncedCount(): int
    {
        return $this->repo
            ->asBounced(true)
            ->select()
            ->count();
    }

    /**
     * @return int
     */
    public function getComplaintCount(): int
    {
        return $this->repo
            ->asComplaint(true)
            ->select()
            ->count();
    }

}
