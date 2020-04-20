<?php

namespace Mailery\Subscriber\Form;

use Mailery\Subscriber\Entity\Group;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use FormManager\Form;
use FormManager\Factory as F;
use Cycle\ORM\Transaction;
use Cycle\ORM\ORMInterface;
use Yiisoft\Security\PasswordHasher;
use Mailery\Subscriber\Repository\GroupRepository;

class GroupForm extends Form
{

    /**
     * @var ORMInterface
     */
    private ORMInterface $orm;

    /**
     * @var Group
     */
    private ?Group $group;

    /**
     * @inheritdoc
     */
    public function __construct(ORMInterface $orm)
    {
        $this->orm = $orm;
        parent::__construct($this->inputs());
    }

    /**
     * @param Group $group
     * @return self
     */
    public function withGroup(Group $group): self
    {
        $this->group = $group;
        $this->offsetSet('', F::submit('Update'));

        $this['name']->setValue($group->getName());

        return $this;
    }

    /**
     * @return Group
     */
    public function save(): Group
    {
        $name = $this['name']->getValue();

        if (($group = $this->group) === null) {
            $group = new Group();
        }

        $group
            ->setName($name)
        ;

        $tr = new Transaction($this->orm);
        $tr->persist($group);
        $tr->run();

        return $group;
    }

    /**
     * @return array
     */
    private function inputs(): array
    {
        /** @var GroupRepository $groupRepo */
        $groupRepo = $this->orm->getRepository(Group::class);

        $nameConstraint = new Constraints\Callback([
            'callback' => function ($value, ExecutionContextInterface $context) use($groupRepo) {
                if (empty($value)) {
                    return;
                }

                $group = $groupRepo->findByName($value, $this->group);
                if ($group !== null) {
                    $context->buildViolation('Group with this name already exists.')
                        ->atPath('name')
                        ->addViolation();
                }
            }
        ]);

        return [
            'name' => F::text('Name')
                ->addConstraint(new Constraints\NotBlank())
                ->addConstraint(new Constraints\Length([
                    'min' => 4,
                ]))
                ->addConstraint(new Constraints\Regex([
                    'pattern' => '/^[a-zA-Z0-9]+$/i',
                ]))
                ->addConstraint($nameConstraint),

            '' => F::submit($this->group === null ? 'Create' : 'Update'),
        ];
    }

}
