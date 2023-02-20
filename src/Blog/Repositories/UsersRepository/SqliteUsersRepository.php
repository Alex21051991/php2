<?php

namespace App\Blog\Repositories\UsersRepository;

use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\User;
use App\Blog\UUID;
use App\Person\Name;

use PDO;
use PDOStatement;

class SqliteUsersRepository implements UsersRepositoryInterface
{
    public function __construct(
        private PDO $connection
    ) {
    }

    public function save(User $user): void
    {
        $statement = $this->connection->prepare(
        'INSERT INTO users (uuid, username, first_name, last_name) VALUES (:uuid, :username, :first_name, :last_name');

        $statement->execute([
            ':uuid' => (string)$user->uuid(),
            ':username' => $user->username(),
            ':first_name' => $user->name()->first(),
            ':last_name' => $user->name()->last(),
            // Это работает, потому что класс UUID имеет магический метод __toString(),
            // который вызывается, когда объект приводится к строке с помощью (string)
        ]);
    }
    /**
     * @throws InvalidArgumentException
     * @throws UserNotFoundException|InvalidArgumentException
     */
    // Также добавим метод для получения пользователя по его UUID
    public function get(UUID $uuid): User
    {
        $statement = $this->connection->prepare(
        'SELECT * FROM users WHERE uuid = ?'
        );
        $statement->execute([(string)$uuid]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            //throw new UserNotFoundException("Cannot get user: $uuid");
            echo "Cannot get user: $uuid";
        }
        return $this->getUser($statement, $uuid);
    }

    // Добавили метод получения пользователя по username

    /**
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws UserNotFoundException
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
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    private function getUser(PDOStatement $statement, string $str): User
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (false === $result) {
            //throw new UserNotFoundException("Cannot find user: $str");
            echo "Cannot find user: $str";
        }
        // Создаём объект пользователя с полем username
        return new User(
            new UUID($result['uuid']),
            new Name($result['first_name'], $result['last_name']),
            $result['username'],
        );
    }

}
