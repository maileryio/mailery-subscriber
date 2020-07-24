<?php

namespace Mailery\Subscriber\Validator;

use Yiisoft\Validator\Validator;
use Yiisoft\I18n\TranslatorInterface;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\HasLength;

class SubscriberValidator extends Validator
{
    public function __construct(
        TranslatorInterface $translator = null,
        string $translationDomain = null,
        string $translationLocale = null
    ) {
        parent::__construct(
            $this->buildRules(),
            $translator,
            $translationDomain,
            $translationLocale
        );
    }

    /**
     * @return array
     */
    private function buildRules(): array
    {
        return [
            'email' => [
                new Required(),
                new Email(),
            ],
            'name' => [
                new Required(),
                (new HasLength())->min(3)->max(255),
            ],
        ];
    }
}
