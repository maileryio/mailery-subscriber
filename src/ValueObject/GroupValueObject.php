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

use Mailery\Brand\Service\BrandInterface;
use Mailery\Subscriber\Form\GroupForm;

class GroupValueObject
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var BrandInterface
     */
    private BrandInterface $brand;

    /**
     * @param GroupForm $form
     * @return self
     */
    public static function fromForm(GroupForm $form): self
    {
        $new = new self();

        $new->name = $form['name']->getValue();

        return $new;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return BrandInterface
     */
    public function getBrand(): BrandInterface
    {
        return $this->brand;
    }

    /**
     * @param BrandInterface $brand
     * @return self
     */
    public function withBrand(BrandInterface $brand): self
    {
        $new = clone $this;
        $new->brand = $brand;

        return $new;
    }
}
