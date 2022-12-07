<?php

namespace Mailery\Subscriber\Messenger\Handler;

use Port\Csv\CsvReader;
use Mailery\Storage\Filesystem\FileInfo;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Importer\Importer;
use Mailery\Subscriber\Importer\Interpreter\SubscriberInterpreter;
use Mailery\Subscriber\Service\ImportCrudService;
use Mailery\Subscriber\ValueObject\ImportValueObject;
use Mailery\Subscriber\Repository\ImportRepository;
use Mailery\Subscriber\Messenger\Message\ImportSubscribers;

class ImportSubscribersHandler
{

    /**
     * @var Import
     */
    private Import $import;

    /**
     * @param FileInfo $fileInfo
     * @param SubscriberInterpreter $interpreter
     * @param ImportCrudService $importCrudService
     * @param ImportRepository $importRepo
     */
    public function __construct(
        private FileInfo $fileInfo,
        private SubscriberInterpreter $interpreter,
        private ImportCrudService $importCrudService,
        private ImportRepository $importRepo
    ) {}

    /**
     * @param ImportSubscribers $message
     */
    public function __invoke(ImportSubscribers $message)
    {
        $this->import = $this->importRepo->findByPK($message->getImportId());

        if ($this->import === null) {
            throw new \RuntimeException('Not found import entity [' . $message->getImportId() . ']');
        }

        try {
            $this->beforeRun();
            $this->doRun();
            $this->afterRun();
        } catch (\Exception $e) {
            $this->thrownRun();

            throw $e;
        }
    }

    /**
     * @return void
     */
    private function beforeRun(): void
    {
        $this->importCrudService->update(
            $this->import,
            ImportValueObject::fromEntity($this->import)->asRunning()
        );
    }

    /**
     * @return void
     */
    private function afterRun(): void
    {
        $this->importCrudService->update(
            $this->import,
            ImportValueObject::fromEntity($this->import)->asCompleted()
        );
    }

    /**
     * @return void
     */
    private function thrownRun(): void
    {
        $this->importCrudService->update(
            $this->import,
            ImportValueObject::fromEntity($this->import)->asErrored()
        );
    }

    /**
     * @return void
     */
    private function doRun(): void
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
