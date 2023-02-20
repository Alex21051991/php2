<?php

namespace App\Blog;

use App\Blog\Exceptions\InvalidArgumentException;

class UUID
{
    // Внутри объекта мы храним UUID как строку
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        private string $uuidString
    ) {
    // Если входная строка не подходит по формату -
    // бросаем исключение InvalidArgumentException
    // (его мы тоже добавили)
    // Таким образом, мы гарантируем, что если объект
    // был создан, то он точно содержит правильный UUID
        if (!$this->uuid_is_valid($uuidString)) {
           // throw new InvalidArgumentException("Malformed UUID: $this->uuidString");
            echo "Malformed UUID: $this->uuidString";
        }
    }

    // А так мы можем сгенерировать новый случайный UUID
    // и получить его в качестве объекта нашего класса
    /**
     * @return static
     * @throws InvalidArgumentException
     */
    public static function random(): self
    {
        return new self(uuid_create(UUID_TYPE_RANDOM));
    }

    public function __toString(): string
    {
        return $this->uuidString;
    }

    private function uuid_is_valid(string $uuidString)
    {
        return $uuidString;
    }

}