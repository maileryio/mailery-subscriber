<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Factory;

use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Validator;

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
                    ->min(3)
                    ->max(255)
                    ->translator($this->translator),
            ],
        ]);
    }
}
