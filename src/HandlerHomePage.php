<?php

declare(strict_types=1);

namespace ShopManager;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

final class HandlerHomePage implements Handler
{
    private ResponseFactoryInterface $factory;
    private Environment $twig;

    public function __construct(ResponseFactoryInterface $factory, Environment $twig)
    {
        $this->factory = $factory;
        $this->twig = $twig;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $response = $this->factory->createResponse();
        $response->getBody()->write($this->twig->render("home.twig"));

        return $response;
    }
}
