<?php

declare(strict_types=1);

namespace ShopManager\Authentication;

use ShopManager\Users\User;
use ShopManager\Users\UserId;
use ShopManager\Users\Users;
use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use PSR7Sessions\Storageless\Session\SessionInterface;
use RuntimeException;

final class Authentication
{
    private Users $users;

    public function __construct(Users $users)
    {
        $this->users = $users;
    }

    public function isLoggedIn(ServerRequestInterface $request): bool
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $userId = $session->get("userId");

        if (!$userId) {
            return false;
        }

        $user =  $this->users->getById(new UserId($userId));

        return $user instanceof User;
    }

    public function getUser(ServerRequestInterface $request): User
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        /** @var string $userId */
        $userId = $session->get("userId");

        if (!$userId) {
            throw new RuntimeException("No user?!");
        }

        return $this->users->getById(new UserId($userId));
    }

    public function logout(ServerRequestInterface $request): void
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $session->clear();
    }

    public function setUser(ServerRequestInterface $request, User $user): void
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $session->set("userId", (string) $user->getId());
    }
}
