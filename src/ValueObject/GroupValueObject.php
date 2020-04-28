<?php

namespace Mailery\Subscriber\ValueObject;

use Mailery\Subscriber\Form\GroupForm;
use Mailery\Brand\Service\BrandInterface;

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
     * @return \self
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
     * @return string
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
