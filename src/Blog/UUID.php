<?php

namespace App\Blog;

use App\Blog\Exceptions\InvalidArgumentException;

class UUID
{
    // Внутри объекта мы храним UUID как строку
    /**
     * @param string $uuidString
     * @throws InvalidArgumentException
     */
    public function __construct(private string $uuidString)
    {
        if (!uuid_is_valid($uuidString)) {
            throw new InvalidArgumentException(
                "Malformed UUID: $this->uuidString"
            );
        }
    }

    /**
     * @return static
     * @throws InvalidArgumentException
     */
    public static function random(): self
    {
        return new self(uuid_create(UUID_TYPE_RANDOM));
    }

    private function uuid_is_valid(string $uuidString): string
    {
        return $uuidString;
    }

    public function __toString(): string
    {
        return $this->uuidString;
    }
}