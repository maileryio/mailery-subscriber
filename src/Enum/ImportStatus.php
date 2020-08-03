<?php

namespace Mailery\Subscriber\Enum;

class ImportStatus
{
    public const PENDING = 1;
    public const RUNNING = 2;
    public const PAUSED = 3;
    public const ERRORED = 4;
    public const COMPLETED = 5;
}
