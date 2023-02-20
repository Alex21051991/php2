<?php

namespace App\Blog\Repositories\UsersRepository;

use App\Blog\Exceptions\InvalidArgumentException;
//use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\User;
use App\Blog\UUID;
use App\Person\Name;

use Exception;
use PDO;
use PDOStatement;

class SqliteUsersRepository implements UsersRepositoryInterface//, PostsRepositoryInterface
{
    public function __construct(
        private PDO $connection
    ) {
    }

    public function save(User $user): void
    {
        $statement = $this->connection->prepare(
        'INSERT INTO users (uuid, first_name, last_name, username) VALUES (:uuid, :first_name, :last_name, :username)');

        $statement->execute([
            ':uuid' => (string)$user->uuid(),
            ':first_name' => $user->name()->first(),
            ':last_name' => $user->name()->last(),
            ':username' => $user->username(),
            // Это работает, потому что класс UUID имеет магический метод __toString(),
            // который вызывается, когда объект приводится к строке с помощью (string)
        ]);
    }
    /**
     * @throws InvalidArgumentException
     */
    // Также добавим метод для получения пользователя по его UUID
    public function get(UUID $uuid): User
    {
        $statement = $this->connection->prepare(
        'SELECT * FROM users WHERE uuid = ?'
        );

        $statement->execute([(string)$uuid]);

        return $this->getUser($statement, $uuid);
    }

    // Добавили метод получения пользователя по username

    /**
     * @throws InvalidArgumentException
     */
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
    /**
     * @param PDOStatement $statement
     * @param string $str
     * @return User
     * @throws InvalidArgumentException
     * @throws Exception
     */
    private function getUser(PDOStatement $statement, string $str): User
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (false === $result) {
            throw new InvalidArgumentException("Cannot find user: $str");
        }
        // Создаём объект пользователя с полем username
        return new User(
            new UUID($result['uuid']),
            new Name($result['first_name'], $result['last_name']),
            $result['username'],
        );
    }

}


