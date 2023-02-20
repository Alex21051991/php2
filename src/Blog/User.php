<?php

namespace App\Blog;

use App\Person\Name;

class User extends Post
{
     private UUID $uuid;
     private Name $name;
     private string $username;

    public function __construct(UUID $uuid, Name $name, string $login)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->username = $login;
    }

    public  function __toString(): string
    {
        return "Юзер $this->uuid с именем $this->name  и логином $this->username." . PHP_EOL;
    }

    /**
     * @return UUID
     */
    public function uuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * @return Name
     */
    public function name(): Name
    {
        return $this->name;
    }

    /**
     * @param Name $name
     */
    public function setName(Name $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function username(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }
}