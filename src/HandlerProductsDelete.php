<?php declare(strict_types=1);

namespace ShopManager;

use ShopManager\Products\ProductId;
use ShopManager\Products\Products;
use JournyIO\SDK\AccountIdentified;
use JournyIO\SDK\Client;
use JournyIO\SDK\Event;
use JournyIO\SDK\UserIdentified;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ShopManager\Shops\ShopId;

final class HandlerProductsDelete implements Handler
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
        $productId = new ProductId($args["productId"]);
        $shopProducts = $this->products->getByShopId($shopId);
        foreach ($shopProducts as $product) {
            if ($product->getId()->equals($productId)) {
                $product->delete();
                $this->products->persist($product);

                $user = $this->authentication->getUser($request);
                $this->client->addEvent(
                    Event::forUserInAccount(
                        "removed_product",
                        UserIdentified::byUserId((string) $user->getId()),
                        AccountIdentified::byAccountId((string) $shopId)
                    )
                        ->withMetadata([
                            "id" => (string) $product->getId(),
                            "name" => $product->getName(),
                        ])
                );
            }
        }

        return $this->factory->createResponse()
            ->withStatus(302)
            ->withAddedHeader("Location", "/shops/{$shopId}/products")
        ;
    }
}
