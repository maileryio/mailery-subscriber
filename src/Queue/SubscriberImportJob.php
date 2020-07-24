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

use Yiisoft\Yii\Queue\Job\JobInterface;
use Mailery\Subscriber\Entity\SubscriberImport;
use Mailery\Subscriber\Importer\Importer;
use Ddeboer\DataImport\Reader\CsvReader;
use Yiisoft\Yii\Queue\Queue;
use Mailery\Storage\Provider\FilesystemProvider;
use Mailery\Subscriber\Importer\Interpreter\SubscriberInterpreter;

class SubscriberImportJob implements JobInterface
{
    /**
     * @var Queue
     */
    private Queue $queue;

    /**
     * @var SubscriberImport
     */
    private SubscriberImport $import;

    /**
     * @var FilesystemProvider
     */
    private FilesystemProvider $filesystemProvider;

    /**
     * @var SubscriberInterpreter
     */
    private SubscriberInterpreter $subscriberInterpreter;

    /**
     * @param Queue $queue
     * @param FilesystemProvider $filesystemProvider
     * @param SubscriberInterpreter $subscriberInterpreter
     */
    public function __construct(Queue $queue, FilesystemProvider $filesystemProvider, SubscriberInterpreter $subscriberInterpreter)
    {
        $this->queue = $queue;
        $this->filesystemProvider = $filesystemProvider;
        $this->subscriberInterpreter = $subscriberInterpreter;
    }

    /**
     * @param SubscriberImport $import
     */
    public function push(SubscriberImport $import)
    {
        $this->import = $import;
        $this->execute();// $this->queue->push($this);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $filesystem = $this->filesystemProvider->getFilesystem($this->import->getFile()->getFilesystem());
        $metaData = stream_get_meta_data($filesystem->readStream($this->import->getFile()->getLocation()));

        $reader = new CsvReader(new \SplFileObject($metaData['uri']));
        $interpreter = $this->subscriberInterpreter
            ->withImport($this->import);

        (new Importer($reader))->import($interpreter);
    }
}
