<?php

declare(strict_types=1);

namespace ShopManager\Authentication;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ShopManager\Authentication\Authentication;

final class RedirectIfNotAuthenticated implements MiddlewareInterface
{
    private ResponseFactoryInterface $factory;
    private Authentication $authentication;

    public function __construct(ResponseFactoryInterface $factory, Authentication $authentication)
    {
        $this->factory = $factory;
        $this->authentication = $authentication;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->authentication->isLoggedIn($request)) {
            return $this->factory->createResponse()
                ->withStatus(302)
                ->withHeader("Location", "/login")
            ;
        }

        return $handler->handle($request);
    }
}
