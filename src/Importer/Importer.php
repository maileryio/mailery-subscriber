<?php

namespace Mailery\Subscriber\Importer;

use Ddeboer\DataImport\Reader;
use Mailery\Subscriber\Importer\ImporterInterface;
use Mailery\Subscriber\Importer\InterpreterInterface;

class Importer implements ImporterInterface
{
    /**
     * @var Reader
     */
    private Reader $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param InterpreterInterface $interpreter
     * @return void
     */
    public function import(InterpreterInterface $interpreter): void
    {
        foreach($this->reader as $row) {
            $interpreter->interpret($row);
        }
    }
}