<?php

namespace App\Blog;

class Post
{

    public function __construct(
        private UUID   $uuid,
        private User   $user,
        private string $title,
        private string $text,
    )
    {
    }

    public function uuid(): UUID
    {
        return $this->uuid;
    }

    public function setUuid(UUID $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): Post
    {
        $this->text = $text;
        return $this;
    }

    public function __toString(): string
    {
        return $this->user . ' пишет: ' . $this->text . PHP_EOL;
    }
}