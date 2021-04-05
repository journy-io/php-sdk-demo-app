<?php declare(strict_types=1);

namespace App;

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
     * @param string $shopId
     *
     * @return Product[]
     */
    private function load(string $shopId): array
    {
        $contents = @file_get_contents($this->getFileName($shopId));

        if (!$contents) {
            return [];
        }

        $json = json_decode($contents, true);

        return array_map(
            fn (array $product) => new Product(
                $product["id"],
                $shopId,
                $product["name"],
                new Money($product["price"]["amount"], new Currency($product["price"]["currency"]))
            ),
            $json
        );
    }

    private function getFileName(string $shopId): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $shopId . ".json";
    }

    /**
     * @param Product[] $products
     * @param string $shopId
     */
    private function write(array $products, string $shopId)
    {
        file_put_contents(
            $this->getFileName($shopId),
            json_encode(
                array_map(
                    fn ($product) => ([
                        "id" => $product->getId(),
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

    public function getByShopId(string $shopId): array
    {
        return $this->load($shopId);
    }

    public function persist(Product $product): void
    {
        $products = $this->load($product->getShopId());
        $products = array_filter($products, fn ($existing) => $existing->getId() !== $product->getId());
        $products[] = $product;
        $this->write($products, $product->getShopId());
    }
}
