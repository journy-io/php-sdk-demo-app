<?php

namespace ShopManager\Users;

interface Users
{
    public function getById(UserId $id): ?User;
    public function getByEmail(Email $email): ?User;
    public function persist(User $user): void;
}
