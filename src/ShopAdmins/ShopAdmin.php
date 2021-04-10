<?php declare(strict_types=1);

namespace ShopManager\ShopAdmins;

use ShopManager\Shops\ShopId;
use ShopManager\Users\UserId;

final class ShopAdmin
{
    private UserId $userId;
    private ShopId $shopId;

    public function __construct(UserId $userId, ShopId $shopId)
    {
        $this->userId = $userId;
        $this->shopId = $shopId;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getShopId(): ShopId
    {
        return $this->shopId;
    }
}
