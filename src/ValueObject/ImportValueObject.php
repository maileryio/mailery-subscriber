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
    private array $fieldsMap;

    /**
     * @param ImportForm $form
     * @return self
     */
    public static function fromForm(ImportForm $form): self
    {
        $new = new self();
        $new->file = $form->getAttributeValue('file');
        $new->groups = $form->getGroups();
        $new->fieldsMap = $form->getAttributeValue('fieldsMap');

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
    public function getFieldsMap(): array
    {
        return $this->fieldsMap;
    }

}
