<?php

namespace Mailery\Subscriber\Messenger\Message;

class ImportSubscribers
{

    /**
     * @param int $importId
     */
    public function __construct(
        private int $importId
    ) {}

    /**
     * @return int
     */
    public function getImportId(): int
    {
        return $this->importId;
    }

}
