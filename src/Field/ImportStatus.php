<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Field;

use Yiisoft\Translator\TranslatorInterface;

class ImportStatus
{
    private const PENDING = 'pending';
    private const RUNNING = 'running';
    private const PAUSED = 'paused';
    private const ERRORED = 'errored';
    private const COMPLETED = 'completed';

    /**
     * @var TranslatorInterface|null
     */
    private ?TranslatorInterface $translator = null;

    /**
     * @param string $value
     */
    private function __construct(
        private string $value
    ) {
        if (!in_array($value, self::getKeys())) {
            throw new \InvalidArgumentException('Invalid passed value: ' . $value);
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public static function getKeys(): array
    {
        return [
            self::PENDING,
            self::RUNNING,
            self::PAUSED,
            self::ERRORED,
            self::COMPLETED,
        ];
    }

    /**
     * @param string $value
     * @return static
     */
    public static function typecast(string $value): static
    {
        return new static($value);
    }

    /**
     * @return self
     */
    public static function asPending(): self
    {
        return new self(self::PENDING);
    }

    /**
     * @return self
     */
    public static function asRunning(): self
    {
        return new self(self::RUNNING);
    }

    /**
     * @return self
     */
    public static function asErrored(): self
    {
        return new self(self::ERRORED);
    }

    /**
     * @return self
     */
    public static function asCompleted(): self
    {
        return new self(self::COMPLETED);
    }

    /**
     * @param TranslatorInterface $translator
     * @return self
     */
    public function withTranslator(TranslatorInterface $translator): self
    {
        $new = clone $this;
        $new->translator = $translator;

        return $new;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        $fnTranslate = function (string $message) {
            if ($this->translator !== null) {
                return $this->translator->translate($message);
            }
            return $message;
        };

        return [
            self::PENDING => $fnTranslate('Pending'),
            self::RUNNING => $fnTranslate('Running'),
            self::PAUSED => $fnTranslate('Paused'),
            self::ERRORED => $fnTranslate('Errored'),
            self::COMPLETED => $fnTranslate('Completed'),
        ][$this->value] ?? 'Unknown';
    }

    /**
     * @return string
     */
    public function getCssClass(): string
    {
        return [
            self::PENDING => 'badge-secondary',
            self::RUNNING => 'badge-info',
            self::PAUSED => 'badge-warning',
            self::ERRORED => 'badge-danger',
            self::COMPLETED => 'badge-success',
        ][$this->value] ?? 'badge-light';
    }
}
