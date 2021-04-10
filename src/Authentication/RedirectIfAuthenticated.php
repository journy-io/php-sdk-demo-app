<?php

declare(strict_types=1);

namespace ShopManager\Authentication;

use ShopManager\Authentication\Authentication;
use ShopManager\ShopAdmins\ShopAdmins;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RedirectIfAuthenticated implements MiddlewareInterface
{
    private ResponseFactoryInterface $factory;
    private Authentication $authentication;
    private ShopAdmins $shopAdmins;

    public function __construct(ResponseFactoryInterface $factory, Authentication $authentication, ShopAdmins $shopAdmins)
    {
        $this->factory = $factory;
        $this->authentication = $authentication;
        $this->shopAdmins = $shopAdmins;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->authentication->isLoggedIn($request)) {
            $user = $this->authentication->getUser($request);
            $admins = $this->shopAdmins->getByUserId($user->getId());

            return $this->factory->createResponse()
                ->withStatus(302)
                ->withHeader("Location", "/shops/{$admins[0]->getShopId()}/products")
            ;
        }

        return $handler->handle($request);
    }
}
