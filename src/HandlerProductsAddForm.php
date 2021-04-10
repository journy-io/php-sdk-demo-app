<?php

declare(strict_types=1);

namespace ShopManager;

use ShopManager\Authentication\Authentication;
use Psr\Http\Message\ResponseFactoryInterface;
use Twig\Environment;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HandlerProductsAddForm implements Handler
{
    private Environment $twig;
    private ResponseFactoryInterface $factory;
    private Authentication $authentication;

    public function __construct(Environment $twig, ResponseFactoryInterface $factory, Authentication $authentication)
    {
        $this->twig = $twig;
        $this->factory = $factory;
        $this->authentication = $authentication;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $this->authentication->getUser($request);
        $response = $this->factory->createResponse();
        $response->getBody()->write(
            $this->twig->render("add-product.twig", [
                'shopId' => $args['shopId'],
                'user' => [
                    "id" => $user->getId(),
                    "first_name" => $user->getFirstName(),
                    "last_name" => $user->getLastName(),
                ],
            ])
        );

        return $response;
    }
}
