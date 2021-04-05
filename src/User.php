<?php declare(strict_types=1);

namespace App;

final class User
{
    private string $id;
    private string $email;
    private string $firstName;
    private string $lastName;

    public function __construct(string $id, string $email, string $firstName, string $lastName)
    {
        $this->id = $id;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
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
}
