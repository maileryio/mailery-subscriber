<?php

namespace Mailery\Subscriber\Widget;

use Yiisoft\Widget\Widget;
use Mailery\Subscriber\Entity\Import;
use Yiisoft\Html\Html;

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

        if ($this->import->getIsPending()) {
            $label = 'Pending';
            Html::addCssClass($options, 'badge badge-secondary');
        } else if ($this->import->getIsRunning()) {
            $label = 'Running';
            Html::addCssClass($options, 'badge badge-info');
        } else if ($this->import->getIsPaused()) {
            $label = 'Paused';
            Html::addCssClass($options, 'badge badge-warning');
        } else if ($this->import->getIsCompleted()) {
            $label = 'Completed';
            Html::addCssClass($options, 'badge badge-success');
        } else if ($this->import->getIsErrored()) {
            $label = 'Completed';
            Html::addCssClass($options, 'badge badge-danger');
        } else {
            $label = 'Unknown';
            Html::addCssClass($options, 'badge badge-light');
        }

        return Html::tag($this->tag, $label, $options);
    }
}