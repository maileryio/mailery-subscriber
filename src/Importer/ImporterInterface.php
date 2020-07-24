<?php

namespace Mailery\Subscriber\Importer;

use Mailery\Subscriber\Importer\InterpreterInterface;

interface ImporterInterface
{
    /**
     * @param InterpreterInterface $interpreter
     */
    public function import(InterpreterInterface $interpreter);
}