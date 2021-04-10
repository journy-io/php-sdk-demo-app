<?php

declare(strict_types=1);

namespace ShopManager\Users;

final class UsersInMemory implements Users
{
    /**
     * @var User[]
     */
    private array $users = [];

    public function getById(UserId $id): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getId()->equals($id)) {
                return $user;
            }
        }

        return null;
    }

    public function getByEmail(Email $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getEmail()->equals($email)) {
                return $user;
            }
        }

        return null;
    }

    public function persist(User $user): void
    {
        $this->users = array_values(
            array_filter($this->users, fn ($existing) => $existing->getId()->equals($user->getId()) === false)
        );
        $this->users[] = $user;
    }
}
