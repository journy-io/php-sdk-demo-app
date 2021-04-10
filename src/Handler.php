<?php

namespace ShopManager;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Handler
{
    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface;
}
