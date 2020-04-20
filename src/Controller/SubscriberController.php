<?php

declare(strict_types=1);

namespace Mailery\Subscriber\Controller;

use Mailery\Subscriber\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SubscriberController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->render('index');
    }
}
