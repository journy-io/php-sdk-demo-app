<?php declare(strict_types=1);

namespace App;

final class ShopAdmin
{
    private string $userId;
    private string $shopId;

    public function __construct(string $userId, string $shopId)
    {
        $this->userId = $userId;
        $this->shopId = $shopId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getShopId(): string
    {
        return $this->shopId;
    }
}
