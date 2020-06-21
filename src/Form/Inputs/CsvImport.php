<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Form\Inputs;

use FormManager\InputInterface;
use FormManager\Inputs\Input;
use FormManager\Node;

class CsvImport extends Input
{
    /**
     * @var array
     */
    protected $validators = [
        'file' => 'file',
        'required' => 'required',
        'accept' => 'accept',
    ];
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string|null $label
     * @param iterable $attributes
     */
    public function __construct(string $label = null, iterable $attributes = [])
    {
        Node::__construct('ui-csv-import', $attributes);

        if (isset($label)) {
            $this->setLabel($label);
        }
    }

    /**
     * @return callable|null
     */
    public function getRenderer(): ?callable
    {
        return function (bool $submitted): self {
            if ($submitted) {
                $this->setAttribute('error', $this->getError());
            }

            return $this;
        };
    }

    /**
     * @param mixed $value
     * @return InputInterface
     */
    public function setValue($value): InputInterface
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
