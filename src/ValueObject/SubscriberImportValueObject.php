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

use Mailery\Brand\Entity\Brand;
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Form\SubscriberImportForm;
use Nyholm\Psr7\UploadedFile;

class SubscriberImportValueObject
{
    /**
     * @var Brand
     */
    private Brand $brand;

    /**
     * @var Group[]
     */
    private array $groups;

    /**
     * @var UploadedFile
     */
    private UploadedFile $file;

    /**
     * @var array
     */
    private array $fieldsMap;

    /**
     * @param SubscriberImportForm $form
     * @return self
     */
    public static function fromForm(SubscriberImportForm $form): self
    {
        $new = new self();

        $new->file = $form['file']->getValue();
        $new->fieldsMap = $form['fields[]']->getValue();

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
     * @return Brand
     */
    public function getBrand(): Brand
    {
        return $this->brand;
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
    public function getFieldsMap(): array
    {
        return $this->fieldsMap;
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
     * @param Group[] $groups
     * @return self
     */
    public function withGroups(array $groups): self
    {
        $new = clone $this;
        $new->groups = $groups;

        return $new;
    }
}
