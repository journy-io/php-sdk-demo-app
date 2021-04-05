<?php

namespace App;

interface Shops
{
    public function getById(string $id): ?Shop;
    public function persist(Shop $shop): void;
}
