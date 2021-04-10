<?php declare(strict_types=1);

namespace ShopManager\Users;

use DateTimeImmutable;
use InvalidArgumentException;

final class User
{
    private UserId $id;
    private Email $email;
    private string $firstName;
    private string $lastName;
    private PhoneNumber $phone;
    private DateTimeImmutable $registeredAt;

    public function __construct(
        UserId $id,
        Email $email,
        string $firstName,
        string $lastName,
        PhoneNumber $phone,
        DateTimeImmutable $registeredAt
    ) {
        if (empty($firstName)) {
            throw new InvalidArgumentException("First name cannot be empty!");
        }

        if (empty($lastName)) {
            throw new InvalidArgumentException("Last name cannot be empty!");
        }

        $this->id = $id;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->registeredAt = $registeredAt;
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getPhone(): PhoneNumber
    {
        return $this->phone;
    }

    public function getRegisteredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }
}
