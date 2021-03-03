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

use FormManager\Factory as F;
use FormManager\Form;
use Mailery\Brand\Entity\Brand;
use Mailery\Brand\BrandLocatorInterface as BrandLocator;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Form\Inputs\CsvImport;
use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Subscriber\Service\ImportCrudService;
use Mailery\Subscriber\ValueObject\ImportValueObject;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Database\Injection\Parameter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

class ImportForm extends Form
{
    /**
     * @var Brand
     */
    private Brand $brand;

    /**
     * @var Import|null
     */
    private ?Import $import = null;

    /**
     * @var GroupRepository
     */
    private GroupRepository $groupRepo;

    /**
     * @var ImportCrudService
     */
    private ImportCrudService $importCrudService;

    /**
     * @param BrandLocator $brandLocator
     * @param GroupRepository $groupRepo
     * @param ImportCrudService $importCrudService
     */
    public function __construct(
        BrandLocator $brandLocator,
        GroupRepository $groupRepo,
        ImportCrudService $importCrudService
    ) {
        $this->brand = $brandLocator->getBrand();
        $this->groupRepo = $groupRepo->withBrand($this->brand);
        $this->importCrudService = $importCrudService;
        parent::__construct($this->inputs());
    }

    /**
     * @param string $csrf
     * @return \self
     */
    public function withCsrf(string $value, string $name = '_csrf'): self
    {
        $this->offsetSet($name, F::hidden($value));

        return $this;
    }

    /**
     * @param Import $import
     * @return self
     */
    public function withImport(Import $import): self
    {
        $this->import = $import;

        return $this;
    }

    /**
     * @return Import|null
     */
    public function import(): ?Import
    {
        if (!$this->isValid()) {
            return null;
        }

        $groupIds = $this['groups']->getValue();

        $groups = $this->groupRepo->findAll([
            'id' => ['in' => new Parameter($groupIds)],
        ]);

        $valueObject = ImportValueObject::fromForm($this)
            ->withBrand($this->brand)
            ->withGroups((array) $groups);

        if (($import = $this->import) === null) {
            $import = $this->importCrudService->create($valueObject);
        } else {
            $this->importCrudService->update($import, $valueObject);
        }

        return $import;
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromServerRequest(Request $request): Form
    {
        parent::loadFromServerRequest($request);

        $parsedBody = (array) $request->getParsedBody();
        $this['groups']->setValue(array_map('intval', $parsedBody['groups'] ?? []));

        $fieldsMap = [];
        foreach (array_keys($this->getFieldsMap()) as $field) {
            if (isset($parsedBody['fields'][$field])) {
                $fieldsMap[$field] = (int) $parsedBody['fields'][$field];
            }
        }

        $this['fields[]']->setValue($fieldsMap);

        return $this;
    }

    /**
     * @return array
     */
    private function inputs(): array
    {
        $groupOptions = $this->getGroupOptions();

        $fileAttributes = [
            'map-fields' => json_encode($this->getFieldsMap()),
        ];

        return [
            'groups' => F::select('Import to groups', $groupOptions, ['multiple' => true])
                ->addConstraint(new Constraints\NotBlank())
                ->addConstraint(new Constraints\Choice([
                    'choices' => array_keys($groupOptions),
                    'multiple' => true,
                ])),
            'file' => (new CsvImport('File in CSV format', $fileAttributes))
                ->addConstraint(new Constraints\Required())
                ->addConstraint(new Constraints\Callback([
                    'callback' => function ($value, ExecutionContextInterface $context) {
                        if (empty($value) || !isset($_FILES['file'])) {
                            return;
                        }

                        $file = $_FILES['file'];

                        $validator = Validation::createValidator();
                        $violations = $validator->validate(
                            new UploadedFile(
                                (string) $file['tmp_name'],
                                (string) $file['name'],
                                (string) $file['type'],
                                (int) $file['error']
                            ),
                            [
                                new Constraints\File([
                                    'maxSize' => '5M',
                                    'mimeTypes' => [
                                        'text/csv',
                                        'text/plain',
                                    ],
                                    'mimeTypesMessage' => 'Please upload a valid CSV file.',
                                ]),
                            ]
                        );

                        foreach ($violations as $violation) {
                            $context->buildViolation($violation->getMessage())
                                ->atPath('file')
                                ->addViolation();
                        }
                    },
                ])),
            'fields[]' => F::hidden(),

            '' => F::submit('Import'),
        ];
    }

    /**
     * @return array
     */
    private function getFieldsMap(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
        ];
    }

    /**
     * @return array
     */
    private function getGroupOptions(): array
    {
        $options = [];
        $groups = $this->groupRepo->findAll();

        foreach ($groups as $group) {
            $options[$group->getId()] = $group->getName();
        }

        return $options;
    }
}
