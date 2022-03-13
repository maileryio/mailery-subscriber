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
    private array $fieldsMap = [];

    /**
     * @var GroupRepository
     */
    private GroupRepository $groupRepo;

    /**
     * @param BrandLocator $brandLocator
     * @param GroupRepository $groupRepo
     */
    public function __construct(
        BrandLocator $brandLocator,
        GroupRepository $groupRepo
    ) {
        $this->groupRepo = $groupRepo->withBrand($brandLocator->getBrand());

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function load(array $data, ?string $formName = null): bool
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
        return $this->fieldsMap;
    }

    /**
     * @return array
     */
    public function getFieldsMap(): array
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
            'fieldsMap' => [
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

//

//
//    /**
//     * @return Import|null
//     */
//    public function import(): ?Import
//    {
//        if (!$this->isValid()) {
//            return null;
//        }
//
//        $groupIds = $this['groups']->getValue();
//
//        $groups = $this->groupRepo->findAll([
//            'id' => ['in' => new Parameter($groupIds)],
//        ]);
//
//        $valueObject = ImportValueObject::fromForm($this)
//            ->withBrand($this->brand)
//            ->withGroups((array) $groups);
//
//        if (($import = $this->import) === null) {
//            $import = $this->importCrudService->create($valueObject);
//        } else {
//            $this->importCrudService->update($import, $valueObject);
//        }
//
//        return $import;
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function loadFromServerRequest(Request $request): Form
//    {
//        parent::loadFromServerRequest($request);
//
//        $parsedBody = (array) $request->getParsedBody();
//        $this['groups']->setValue(array_map('intval', $parsedBody['groups'] ?? []));
//
//        $fieldsMap = [];
//        foreach (array_keys($this->getFieldsMap()) as $field) {
//            if (isset($parsedBody['fields'][$field])) {
//                $fieldsMap[$field] = (int) $parsedBody['fields'][$field];
//            }
//        }
//
//        $this['fields[]']->setValue($fieldsMap);
//
//        return $this;
//    }
//
//    /**
//     * @return array
//     */
//    private function inputs(): array
//    {
//        $groupOptions = $this->getGroupListOptions();
//
//        $fileAttributes = [
//            'map-fields' => json_encode($this->getFieldsMap()),
//        ];
//
//        return [
//            'groups' => F::select('Import to groups', $groupOptions, ['multiple' => true])
//                ->addConstraint(new Constraints\NotBlank())
//                ->addConstraint(new Constraints\Choice([
//                    'choices' => array_keys($groupOptions),
//                    'multiple' => true,
//                ])),
//            'file' => (new CsvImport('File in CSV format', $fileAttributes))
//                ->addConstraint(new Constraints\Required())
//                ->addConstraint(new Constraints\Callback([
//                    'callback' => function ($value, ExecutionContextInterface $context) {
//                        if (empty($value) || !isset($_FILES['file'])) {
//                            return;
//                        }
//
//                        $file = $_FILES['file'];
//
//                        $validator = Validation::createValidator();
//                        $violations = $validator->validate(
//                            new UploadedFile(
//                                (string) $file['tmp_name'],
//                                (string) $file['name'],
//                                (string) $file['type'],
//                                (int) $file['error']
//                            ),
//                            [
//                                new Constraints\File([
//                                    'maxSize' => '5M',
//                                    'mimeTypes' => [
//                                        'text/csv',
//                                        'text/plain',
//                                    ],
//                                    'mimeTypesMessage' => 'Please upload a valid CSV file.',
//                                ]),
//                            ]
//                        );
//
//                        foreach ($violations as $violation) {
//                            $context->buildViolation($violation->getMessage())
//                                ->atPath('file')
//                                ->addViolation();
//                        }
//                    },
//                ])),
//            'fields[]' => F::hidden(),
//
//            '' => F::submit('Import'),
//        ];
//    }
//

//
}
