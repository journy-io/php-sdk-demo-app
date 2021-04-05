<?php declare(strict_types=1);

namespace App;

final class UsersInMemory implements Users
{
    /**
     * @var User[]
     */
    private array $users = [];

    public function getById(string $id): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getId() === $id) {
                return $user;
            }
        }

        return null;
    }

    public function getByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }

        return null;
    }

    public function persist(User $user): void
    {
        $this->users = array_filter($this->users, fn ($existing) => $existing->getId() !== $user->getId());
        $this->users[] = $user;
    }
}
