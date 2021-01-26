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
use Mailery\Brand\Service\BrandLocatorInterface as BrandLocator;
use Mailery\Subscriber\Entity\Group;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Subscriber\Repository\GroupRepository;
use Mailery\Subscriber\Repository\SubscriberRepository;
use Mailery\Subscriber\Service\SubscriberCrudService;
use Mailery\Subscriber\ValueObject\SubscriberValueObject;
use Mailery\Widget\Form\Groups\RadioGroup;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Database\Injection\Parameter;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SubscriberForm extends Form
{
    /**
     * @var Brand
     */
    private Brand $brand;

    /**
     * @var Subscriber|null
     */
    private ?Subscriber $subscriber = null;

    /**
     * @var GroupRepository
     */
    private GroupRepository $groupRepo;

    /**
     * @var SubscriberRepository
     */
    private SubscriberRepository $subscriberRepo;

    /**
     * @var SubscriberCrudService
     */
    private SubscriberCrudService $subscriberCrudService;

    /**
     * @param BrandLocator $brandLocator
     * @param GroupRepository $groupRepo
     * @param SubscriberRepository $subscriberRepo
     * @param SubscriberCrudService $subscriberCrudService
     */
    public function __construct(
        BrandLocator $brandLocator,
        GroupRepository $groupRepo,
        SubscriberRepository $subscriberRepo,
        SubscriberCrudService $subscriberCrudService
    ) {
        $this->brand = $brandLocator->getBrand();
        $this->groupRepo = $groupRepo->withBrand($this->brand);
        $this->subscriberRepo = $subscriberRepo->withBrand($this->brand);
        $this->subscriberCrudService = $subscriberCrudService;

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
     * @param Subscriber $subscriber
     * @return self
     */
    public function withSubscriber(Subscriber $subscriber): self
    {
        $this->subscriber = $subscriber;
        $this->offsetSet('', F::submit('Update'));

        $this['name']->setValue($subscriber->getName());
        $this['email']->setValue($subscriber->getEmail());
        $this['groups']->setValue(array_map(
            function (Group $group) {
                return $group->getId();
            },
            $subscriber->getGroups()->toArray()
        ));
        $this['confirmed']->setValue($subscriber->getConfirmed() ? 'yes' : 'no');

        return $this;
    }

    /**
     * @return Subscriber|null
     */
    public function save(): ?Subscriber
    {
        if (!$this->isValid()) {
            return null;
        }

        $groupIds = $this['groups']->getValue();

        $groups = $this->groupRepo->findAll([
            'id' => ['in' => new Parameter($groupIds)],
        ]);

        $valueObject = SubscriberValueObject::fromForm($this)
            ->withBrand($this->brand)
            ->withGroups((array) $groups);

        if (($subscriber = $this->subscriber) === null) {
            $subscriber = $this->subscriberCrudService->create($valueObject);
        } else {
            $this->subscriberCrudService->update($subscriber, $valueObject);
        }

        return $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromServerRequest(Request $request): Form
    {
        parent::loadFromServerRequest($request);

        $parsedBody = (array) $request->getParsedBody();
        $this['groups']->setValue($parsedBody['groups'] ?? []);

        return $this;
    }

    /**
     * @return array
     */
    private function inputs(): array
    {
        $uniqueEmailConstraint = new Constraints\Callback([
            'callback' => function ($value, ExecutionContextInterface $context) {
                if (empty($value)) {
                    return;
                }

                $subscriber = $this->subscriberRepo->findByEmail($value, $this->subscriber);
                if ($subscriber !== null) {
                    $context->buildViolation('Subscriber with this email already exists.')
                        ->atPath('email')
                        ->addViolation();
                }
            },
        ]);

        $groupOptions = $this->getGroupOptions();

        return [
            'groups' => F::select('Add to groups', $groupOptions, ['multiple' => true])
                ->addConstraint(new Constraints\NotBlank())
                ->addConstraint(new Constraints\Choice([
                    'choices' => array_keys($groupOptions),
                    'multiple' => true,
                ])),
            'name' => F::text('Name')
                ->addConstraint(new Constraints\NotBlank()),
            'email' => F::text('Email')
                ->addConstraint(new Constraints\NotBlank())
                ->addConstraint(new Constraints\Email())
                ->addConstraint($uniqueEmailConstraint),
            'confirmed' => (new RadioGroup(
                'Confirmed',
                [
                    'yes' => F::radio('Yes'),
                    'no' => F::radio('No'),
                ]
            ))->setValue('yes'),

            '' => F::submit($this->subscriber === null ? 'Create' : 'Update'),
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
