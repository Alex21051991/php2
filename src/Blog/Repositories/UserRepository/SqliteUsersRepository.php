<?php

//namespace App\Repositories;

namespace App\Blog\Repositories\UsersRepository;
use App\Blog\Name;
use App\Blog\User;
use App\Blog\UUID;
use \PDO;

class SqliteUsersRepository implements UsersRepositoryInterface
{
    public function __construct(
        private PDO $connection
    ) {
    }

    public function save(User $user): void
    {
        $statement = $this->connection->prepare(
        'INSERT INTO users (uuid, username, first_name, last_name) VALUES (:uuid), :username, :first_name, :last_name'
        );
        $statement->execute([
            ':uuid' => (string)$user->uuid(),
            ':username' => $user->username(),
            ':first_name' => $user->name()->first(),
            ':last_name' => $user->name()->last(),
            // Это работает, потому что класс UUID имеет магический метод __toString(),
            // который вызывается, когда объект приводится к строке с помощью (string)
        ]);
    }
    // Также добавим метод для получения пользователя по его UUID
    public function get(UUID $uuid): User
    {
        $statement = $this->connection->prepare(
        'SELECT * FROM users WHERE uuid = :uuid'
        );
        $statement->execute([
            ':uuid' => (string)$uuid,
        ]);
        return $this->getUser($statement, $uuid);
    }

    // Добавили метод получения пользователя по username
    public function getByUsername(string $username): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE username = :username'
        );
        $statement->execute([
            ':username' => $username,
        ]);
        return $this->getUser($statement, $username);
    }
// Вынесли общую логику в отдельный приватный метод
    private function getUser(PDOStatement $statement, string $username): User
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (false === $result) {
            throw new UserNotFoundException(
                "Cannot find user: $username"
            );
        }
        // Создаём объект пользователя с полем username
        return new User(
            new UUID($result['uuid']),
            $result['username'],
            new Name($result['first_name'], $result['last_name'])
        );
    }
}
