<?php

namespace App;

interface Users
{
    public function getById(string $id): ?User;
    public function getByEmail(string $email): ?User;
    public function persist(User $user): void;
}
