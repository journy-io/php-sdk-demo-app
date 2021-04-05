<?php

namespace App;

interface Products
{
    /**
     * @param string $shopId
     *
     * @return Product[]
     */
    public function getByShopId(string $shopId): array;
    public function persist(Product $product): void;
}
