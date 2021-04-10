<?php

namespace ShopManager\Products;

use ShopManager\Shops\ShopId;

interface Products
{
    /**
     * @param ShopId $shopId
     *
     * @return Product[]
     */
    public function getByShopId(ShopId $shopId): array;
    public function persist(Product $product): void;
}
