<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Form;

use Mailery\Brand\BrandLocatorInterface as BrandLocator;
use Mailery\Subscriber\Repository\GroupRepository;
use Spiral\Database\Injection\Parameter;
use Yiisoft\Form\FormModel;
use HttpSoft\Message\UploadedFile;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\InRange;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\RuleSet;

class ImportForm extends FormModel
{

    /**
     * @var UploadedFile|null
     */
    private ?UploadedFile $file = null;

    /**
     * @var array
     */
    private array $groups = [];

    /**
     * @var array
     */
    private array $fields = [];

    /**
     * @param GroupRepository $groupRepo
     * @param BrandLocator $brandLocator
     */
    public function __construct(
        private GroupRepository $groupRepo,
        BrandLocator $brandLocator
    ) {
        $this->groupRepo = $groupRepo->withBrand($brandLocator->getBrand());
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function load(array|object|null $data, ?string $formName = null): bool
    {
        $scope = $formName ?? $this->getFormName();

        if (isset($data[$scope]['groups'])) {
            $data[$scope]['groups'] = array_filter((array) $data[$scope]['groups']);
        }

        return parent::load($data, $formName);
    }

    /**
     * @return UploadedFile|null
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * @return array
     */
    public function getGroups(): array
    {
        if (empty($this->groups)) {
            return [];
        }

        return $this->groupRepo->findAll([
            'id' => ['in' => new Parameter($this->groups, \PDO::PARAM_INT)],
        ]);
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getFieldLabels(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
        ];
    }

    /**
     * @return array
     */
    public function getAttributeLabels(): array
    {
        return [
            'file' => 'File in CSV format',
            'groups' => 'Import to groups',
        ];
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        return [
            'file' => [
                Required::rule(),
            ],
            'groups' => [
                Required::rule(),
                Each::rule(new RuleSet([
                    InRange::rule(array_keys($this->getGroupListOptions())),
                ]))->message('{error}')
            ],
            'fields' => [
                Required::rule(),
            ],
        ];
    }

    /**
     * @return array
     */
    public function getGroupListOptions(): array
    {
        $options = [];
        $groups = $this->groupRepo->findAll();

        foreach ($groups as $group) {
            $options[$group->getId()] = $group->getName();
        }

        return $options;
    }
}
