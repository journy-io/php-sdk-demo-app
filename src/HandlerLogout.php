<?php declare(strict_types=1);

namespace ShopManager;

use JournyIO\SDK\Client;
use JournyIO\SDK\Event;
use JournyIO\SDK\UserIdentified;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HandlerLogout implements Handler
{
    private Client $client;
    private ResponseFactoryInterface $factory;
    private Authentication $authentication;

    public function __construct(
        Client $client,
        ResponseFactoryInterface $factory,
        Authentication $authentication
    ) {
        $this->client = $client;
        $this->factory = $factory;
        $this->authentication = $authentication;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $this->authentication->getUser($request);
        $this->authentication->logout($request);

        $this->client->addEvent(
            Event::forUser(
                "logged_out",
                UserIdentified::byUserId((string) $user->getId())
            )
        );

        return $this->factory->createResponse()
            ->withStatus(302)
            ->withAddedHeader("Location", '/')
        ;
    }
}
