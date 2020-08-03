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

use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Importer\Importer;
use Ddeboer\DataImport\Reader\CsvReader;
use Yiisoft\Yii\Queue\Queue;
use Mailery\Storage\Provider\FilesystemProvider;
use Mailery\Subscriber\Importer\Interpreter\SubscriberInterpreter;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Transaction;

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
     * @var FilesystemProvider
     */
    private FilesystemProvider $filesystemProvider;

    /**
     * @var SubscriberInterpreter
     */
    private SubscriberInterpreter $subscriberInterpreter;

    /**
     * @param ORMInterface $orm
     * @param Queue $queue
     * @param FilesystemProvider $filesystemProvider
     * @param SubscriberInterpreter $subscriberInterpreter
     */
    public function __construct(ORMInterface $orm, Queue $queue, FilesystemProvider $filesystemProvider, SubscriberInterpreter $subscriberInterpreter)
    {
        $this->orm = $orm;
        $this->queue = $queue;
        $this->filesystemProvider = $filesystemProvider;
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
     * @inheritdoc
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
        $filesystem = $this->filesystemProvider->getFilesystem($this->import->getFile()->getFilesystem());
        $metaData = stream_get_meta_data($filesystem->readStream($this->import->getFile()->getLocation()));

        $reader = new CsvReader(new \SplFileObject($metaData['uri']));
        $interpreter = $this->subscriberInterpreter
            ->withImport($this->import);

        (new Importer($reader))->import($interpreter);
    }
}
