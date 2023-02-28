<?php
namespace App\Blog;

use App\Person\Name;

class User
{
    public function __construct(
        private UUID   $uuid,
        private Name   $name,
        private string $username,
        private string $hashedPassword,
    )
    {
    }

    public function hashedPassword(): string
    {
        return $this->hashedPassword;
    }

    // Функция для вычисления хеша
    private static function hash(string $password, UUID $uuid): string
    {
        return hash('sha256',  $uuid . $password);
    }

    // Функция для проверки предъявленного пароля

    /**
     * @param string $password
     * @return bool
     */
    public function checkPassword(string $password): bool
    {
        return $this->hashedPassword === self::hash($password, $this->uuid);
    }

    public function __toString(): string
    {
        //return "Юзер $this->uuid с именем $this->name и логином $this->username." . PHP_EOL;
        return $this->uuid;
    }

    // Функция для создания нового пользователя
    public static function createFrom(
        string $username,
        string $password,
        Name   $name
    ): self
    {
        $uuid = UUID::random();
        return new self(
            $uuid,
            $name,
            $username,
            self::hash($password, $uuid),
        );
    }

    public function uuid(): UUID
    {
        return $this->uuid;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function setName(Name $name): void
    {
        $this->name = $name;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }
}