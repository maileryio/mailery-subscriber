<?php

namespace Mailery\Subscriber\Factory;

use Yiisoft\Validator\Validator;
use Yiisoft\I18n\TranslatorInterface;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\HasLength;

class ValidatorFactory
{

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return Validator
     */
    public function createSubscriberValidator(): Validator
    {
        return new Validator([
            'email' => [
                new Required(),
                new Email(),
            ],
            'name' => [
                new Required(),
                (new HasLength())
                    ->translator($this->translator)
                    ->min(3)
                    ->max(255),
            ],
        ]);
    }
}
