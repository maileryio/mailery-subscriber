<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Enum;

class ImportStatus
{
    public const PENDING = 1;
    public const RUNNING = 2;
    public const PAUSED = 3;
    public const ERRORED = 4;
    public const COMPLETED = 5;
}
