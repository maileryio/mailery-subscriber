<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Importer;

use Port\Reader;

class Importer implements ImporterInterface
{
    /**
     * @param Reader $reader
     */
    public function __construct(
        private Reader $reader
    ) {}

    /**
     * @param InterpreterInterface $interpreter
     * @return void
     */
    public function import(InterpreterInterface $interpreter): void
    {
        foreach ($this->reader as $row) {
            $interpreter->interpret($row);
        }
    }
}
