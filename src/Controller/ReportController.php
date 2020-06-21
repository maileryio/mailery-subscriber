<?php

declare(strict_types=1);

/**
 * Subscriber module for Mailery Platform
 * @link      https://github.com/maileryio/mailery-subscriber
 * @package   Mailery\Subscriber
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2020, Mailery (https://mailery.io/)
 */

namespace Mailery\Subscriber\Controller;

use Mailery\Subscriber\WebController;
use Psr\Http\Message\ResponseInterface as Response;

class ReportController extends WebController
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('index');
    }
}
