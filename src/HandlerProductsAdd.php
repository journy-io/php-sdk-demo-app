<?php declare(strict_types=1);

namespace ShopManager;

use ShopManager\Products\Product;
use ShopManager\Products\ProductId;
use ShopManager\Products\Products;
use JournyIO\SDK\AccountIdentified;
use JournyIO\SDK\Client;
use JournyIO\SDK\Event;
use JournyIO\SDK\UserIdentified;
use Money\Money;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ShopManager\Shops\ShopId;

final class HandlerProductsAdd implements Handler
{
    private Authentication $authentication;
    private Products $products;
    private Client $client;
    private ResponseFactoryInterface $factory;

    public function __construct(Authentication $authentication, Products $products, Client $client, ResponseFactoryInterface $factory)
    {
        $this->authentication = $authentication;
        $this->products = $products;
        $this->client = $client;
        $this->factory = $factory;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $shopId = new ShopId($args["shopId"]);
        $body = $request->getParsedBody();

        $product = new Product(
            ProductId::generate(),
            $shopId,
            $body["name"],
            Money::EUR($body["price"] * 100)
        );

        $this->products->persist($product);

        $user = $this->authentication->getUser($request);
        $this->client->addEvent(
            Event::forUserInAccount(
                "added_product",
                UserIdentified::byUserId((string) $user->getId()),
                AccountIdentified::byAccountId((string) $shopId)
            )
                ->withMetadata([
                    "id" => (string) $product->getId(),
                    "name" => $body["name"],
                    "price" => (int) $body["price"],
                ])
        );

        return $this->factory->createResponse()
            ->withStatus(302)
            ->withAddedHeader("Location", "/shops/{$shopId}/products")
        ;
    }
}
