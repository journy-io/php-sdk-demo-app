<?php

namespace ShopManager\Shops;

interface Shops
{
    public function getById(ShopId $id): ?Shop;
    public function persist(Shop $shop): void;
}
