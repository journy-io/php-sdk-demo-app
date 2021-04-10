<?php declare(strict_types=1);

namespace ShopManager\Products;

use ShopManager\Shops\ShopId;
use InvalidArgumentException;
use Money\Currency;
use Money\Money;

final class ProductsJSONFile implements Products
{
    private string $directory;

    public function __construct(string $directory)
    {
        if (is_dir($directory) === false || is_readable($directory) === false) {
            throw new InvalidArgumentException("Invalid directory");
        }

        $this->directory = $directory;
    }

    /**
     * @param ShopId $shopId
     *
     * @return Product[]
     */
    private function load(ShopId $shopId): array
    {
        $contents = @file_get_contents($this->getFileName($shopId));

        if (!$contents) {
            return [];
        }

        $json = json_decode($contents, true);

        return array_map(
            fn (array $product) => new Product(
                new ProductId($product["id"]),
                $shopId,
                $product["name"],
                new Money($product["price"]["amount"], new Currency($product["price"]["currency"]))
            ),
            $json
        );
    }

    private function getFileName(ShopId $shopId): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $shopId . ".json";
    }

    /**
     * @param Product[] $products
     * @param ShopId $shopId
     */
    private function write(array $products, ShopId $shopId)
    {
        file_put_contents(
            $this->getFileName($shopId),
            json_encode(
                array_map(
                    fn ($product) => ([
                        "id" => (string) $product->getId(),
                        "name" => $product->getName(),
                        "price" => [
                            "amount" => $product->getPrice()->getAmount(),
                            "currency" => $product->getPrice()->getCurrency(),
                        ],
                    ]),
                    $products
                ),
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
            )
        );
    }

    public function getByShopId(ShopId $shopId): array
    {
        return $this->load($shopId);
    }

    public function persist(Product $product): void
    {
        $products = $this->load($product->getShopId());
        $products = array_filter($products, fn ($existing) => $existing->getId()->equals($product->getId()) === false);
        if ($product->isDeleted() === false) {
            $products[] = $product;
        }
        $this->write($products, $product->getShopId());
    }
}
