<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Queue;

use Cycle\ORM\ORMInterface;
use Ddeboer\DataImport\Reader\CsvReader;
use Mailery\Storage\Filesystem\FileInfo;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Importer\Importer;
use Mailery\Subscriber\Importer\Interpreter\SubscriberInterpreter;
use Yiisoft\Yii\Cycle\Data\Writer\EntityWriter;
use Mailery\Subscriber\Fields\ImportStatus;

class ImportJob
{
    /**
     * @param ORMInterface $orm
     * @param FileInfo $fileInfo
     * @param SubscriberInterpreter $interpreter
     */
    public function __construct(
        private ORMInterface $orm,
        private FileInfo $fileInfo,
        private SubscriberInterpreter $interpreter
    ) {}

    /**
     * @param Import $import
     */
    public function push(Import $import)
    {
        $this->import = $import;
        $this->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $this->beforeExecute();
            $this->doExecute();
            $this->afterExecute();
        } catch (\Exception $e) {
            $this->thrownExecute();

            throw $e;
        }
    }

    /**
     * @return void
     */
    private function beforeExecute(): void
    {
        $this->import->setStatus(ImportStatus::asRunning());

        (new EntityWriter($this->orm))->write([$this->import]);
    }

    /**
     * @return void
     */
    private function afterExecute(): void
    {
        $this->import->setStatus(ImportStatus::asCompleted());

        (new EntityWriter($this->orm))->write([$this->import]);
    }

    /**
     * @return void
     */
    private function thrownExecute(): void
    {
        $this->import->setStatus(ImportStatus::asErrored());

        (new EntityWriter($this->orm))->write([$this->import]);
    }

    /**
     * @return void
     */
    private function doExecute(): void
    {
        $stream = $this->fileInfo
            ->withFile($this->import->getFile())
            ->getStream();

        $reader = new CsvReader(new \SplFileObject($stream->getMetadata('uri')));
        $interpreter = $this->interpreter
            ->withImport($this->import);

        (new Importer($reader))->import($interpreter);
    }
}
