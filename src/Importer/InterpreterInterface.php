<?php

namespace Mailery\Subscriber\Importer;

interface InterpreterInterface
{
    /**
     * @param mixed $line
     * @return void
     */
    public function interpret($line): void;
}