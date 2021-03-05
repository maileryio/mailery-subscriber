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
use Cycle\ORM\Transaction;
use Ddeboer\DataImport\Reader\CsvReader;
use Mailery\Storage\Filesystem\FileInfo;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Importer\Importer;
use Mailery\Subscriber\Importer\Interpreter\SubscriberInterpreter;
use Yiisoft\Yii\Queue\Queue;

class ImportJob
{
    /**
     * @var ORMInterface $orm
     */
    private ORMInterface $orm;

    /**
     * @var Queue
     */
    private Queue $queue;

    /**
     * @var Import
     */
    private Import $import;

    /**
     * @var FileInfo
     */
    private FileInfo $fileInfo;

    /**
     * @var SubscriberInterpreter
     */
    private SubscriberInterpreter $subscriberInterpreter;

    /**
     * @param ORMInterface $orm
     * @param Queue $queue
     * @param FileInfo $fileInfo
     * @param SubscriberInterpreter $subscriberInterpreter
     */
    public function __construct(
        ORMInterface $orm,
        Queue $queue,
        FileInfo $fileInfo,
        SubscriberInterpreter $subscriberInterpreter
    ) {
        $this->orm = $orm;
        $this->queue = $queue;
        $this->fileInfo = $fileInfo;
        $this->subscriberInterpreter = $subscriberInterpreter;
    }

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
        $this->import->setIsRunning();

        $transaction = new Transaction($this->orm);
        $transaction->persist($this->import);
        $transaction->run();
    }

    /**
     * @return void
     */
    private function afterExecute(): void
    {
        $this->import->setIsCompleted();

        $transaction = new Transaction($this->orm);
        $transaction->persist($this->import);
        $transaction->run();
    }

    /**
     * @return void
     */
    private function thrownExecute(): void
    {
        $this->import->setIsErrored();

        $transaction = new Transaction($this->orm);
        $transaction->persist($this->import);
        $transaction->run();
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
        $interpreter = $this->subscriberInterpreter
            ->withImport($this->import);

        (new Importer($reader))->import($interpreter);
    }
}
