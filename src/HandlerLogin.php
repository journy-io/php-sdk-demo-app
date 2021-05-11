<?php

declare(strict_types=1);

namespace ShopManager;

use ShopManager\Authentication\Authentication;
use ShopManager\Products\Products;
use ShopManager\ShopAdmins\ShopAdmins;
use ShopManager\Shops\Shops;
use ShopManager\Users\Email;
use ShopManager\Users\Users;
use JournyIO\SDK\Client;
use JournyIO\SDK\Event;
use JournyIO\SDK\UserIdentified;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HandlerLogin implements Handler
{
    private Client $client;
    private Users $users;
    private ShopAdmins $shopAdmins;
    private Shops $shops;
    private Products $products;
    private ResponseFactoryInterface $factory;
    private Authentication $authentication;

    public function __construct(
        Client $client,
        Users $users,
        ShopAdmins $shopAdmins,
        Shops $shops,
        Products $products,
        ResponseFactoryInterface $factory,
        Authentication $authentication
    ) {
        $this->client = $client;
        $this->users = $users;
        $this->shopAdmins = $shopAdmins;
        $this->shops = $shops;
        $this->products = $products;
        $this->factory = $factory;
        $this->authentication = $authentication;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $form = $request->getParsedBody();

        if (!$form) {
            return $this->factory->createResponse()
                ->withStatus(302)
                ->withAddedHeader("Location", "/login")
            ;
        }

        $user = $this->users->getByEmail(new Email($form["email"]));

        if (!$user) {
            return $this->factory->createResponse()
                ->withStatus(302)
                ->withAddedHeader("Location", "/login")
            ;
        }

        $this->authentication->setUser($request, $user);

        $this->client->upsertUser([
            "userId" => (string) $user->getId(),
            "email" => (string) $user->getEmail(),
            "properties" => [
                "first_name" => $user->getFirstName(),
                "last_name" => $user->getLastName(),
                "phone" => (string) $user->getPhone(),
                "registered_at" => $user->getRegisteredAt(),
            ],
        ]);

        $cookies = $request->getCookieParams();
        if (isset($cookies["__journey"])) {
            $this->client->link([
                "userId" => (string) $user->getId(),
                "deviceId" => $cookies["__journey"],
            ]);
        }

        $this->client->addEvent(
            Event::forUser(
                "logged_in",
                UserIdentified::byUserId((string) $user->getId())
            )
        );

        $shopAdmins = $this->shopAdmins->getByUserId($user->getId());
        foreach ($shopAdmins as $admin) {
            $shop = $this->shops->getById($admin->getShopId());

            if ($shop) {
                $this->client->upsertAccount([
                    "accountId" => (string) $shop->getId(),
                    "properties" => [
                        "name" => $shop->getName(),
                        "products" => count($this->products->getByShopId($admin->getShopId())),
                        "registered_at" => $shop->getCreatedAt(),
                    ],
                    "members" => array_map(
                        fn ($shopAdmin) => ["userId" => (string) $shopAdmin->getUserId()],
                        $this->shopAdmins->getByShopId($shop->getId())
                    )
                ]);
            }
        }

        return $this->factory->createResponse()
            ->withStatus(302)
            ->withAddedHeader("Location", "/shops/{$shopAdmins[0]->getShopId()}/products")
        ;
    }
}
