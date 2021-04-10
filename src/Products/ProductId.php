<?php declare(strict_types=1);

namespace ShopManager\Products;

use InvalidArgumentException;

final class ProductId
{
    private string $id;

    public function __construct(string $id)
    {
        if (empty($id)) {
            throw new InvalidArgumentException("Product ID cannot be empty!");
        }

        $this->id = $id;
    }

    public function equals(ProductId $id): bool
    {
        return $this->id === $id->id;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public static function generate(): ProductId
    {
        return new ProductId(uniqid("product_", false));
    }
}
