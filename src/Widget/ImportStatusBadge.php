<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Widget;

use Mailery\Subscriber\Entity\Import;
use Yiisoft\Html\Html;
use Yiisoft\Widget\Widget;

class ImportStatusBadge extends Widget
{
    /**
     * @var string|null
     */
    private ?string $tag = 'span';

    /**
     * @var array
     */
    private array $options = [];

    /**
     * @var Import
     */
    private Import $import;

    /**
     * @param string $tag
     * @return self
     */
    public function tag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @param array $options
     * @return self
     */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param Import $import
     * @return self
     */
    public function import(Import $import): self
    {
        $this->import = $import;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function run(): string
    {
        $options = $this->options;

        Html::addCssClass($options, 'badge ' . $this->import->getStatus()->getCssClass());

        return Html::tag(
                $this->tag,
                $this->import->getStatus()->getLabel(),
                $options
            )->render();
    }
}
