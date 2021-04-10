<?php

declare(strict_types=1);

namespace ShopManager;

use ShopManager\Authentication\Authentication;
use ShopManager\ShopAdmins\ShopAdmin;
use ShopManager\ShopAdmins\ShopAdmins;
use ShopManager\Products\Products;
use ShopManager\Shops\ShopId;
use ShopManager\Shops\Shops;
use ShopManager\Users\Users;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use NumberFormatter;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use PSR7Sessions\Storageless\Session\SessionInterface;
use Twig\Environment;

final class HandlerProductsList implements Handler
{
    private ShopAdmins $admins;
    private Environment $twig;
    private ResponseFactoryInterface $factory;
    private Products $products;
    private Shops $shops;
    private Authentication $authentication;

    public function __construct(ShopAdmins $admins, Environment $twig, ResponseFactoryInterface $factory, Products $products, Shops $shops, Authentication $authentication)
    {
        $this->admins = $admins;
        $this->twig = $twig;
        $this->factory = $factory;
        $this->products = $products;
        $this->shops = $shops;
        $this->authentication = $authentication;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $this->authentication->getUser($request);
        $admins = $this->admins->getByUserId($user->getId());
        $numberFormatter = new NumberFormatter("en_UK", NumberFormatter::DECIMAL);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());
        $response = $this->factory->createResponse();
        $response->getBody()->write(
            $this->twig->render(
                "products.twig",
                [
                    'shopId' => $args["shopId"],
                    'shops' => array_map(
                        function (ShopAdmin $admin) {
                            $shop = $this->shops->getById($admin->getShopId());

                            return [
                                "id" => (string) $shop->getId(),
                                "name" => $shop->getName(),
                            ];
                        },
                        $admins
                    ),
                    'user' => [
                        "id" => (string) $user->getId(),
                        "first_name" => $user->getFirstName(),
                        "last_name" => $user->getLastName(),
                    ],
                    'products' => array_map(
                        fn ($product) => ([
                            "id" => (string) $product->getId(),
                            "name" => $product->getName(),
                            "price" => $moneyFormatter->format($product->getPrice())
                        ]),
                        $this->products->getByShopId(new ShopId($args["shopId"]))
                    )
                ]
            )
        );

        return $response;
    }
}
