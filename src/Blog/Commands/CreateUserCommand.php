<?php

namespace App\Blog\Commands;

use App\Blog\Exceptions\ArgumentsException;
use App\Blog\Exceptions\CommandException;
use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\User;
use App\Blog\UUID;
use App\Person\Name;
use Psr\Log\LoggerInterface;

class CreateUserCommand
{
    // Команда зависит от контракта репозитория пользователей, а не от конкретной реализации
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
        private LoggerInterface $logger,
    )
    {
    }

    /**
     * @param Arguments $arguments
     * @return void
     * @throws ArgumentsException|CommandException
     */
    public function handle(Arguments $arguments): void
    {
        $this->logger->info("Create user command started");

        $username = $arguments->get('username');

        // Проверяем, существует ли пользователь в репозитории
        if ($this->userExists($username)) {
            // Логируем сообщение с уровнем WARNING
            $this->logger->warning("User already exists: $username");
            //return;
            // Бросаем исключение, если пользователь уже существует
            throw new CommandException("User already exists: $username");
        }

        // Сохраняем пользователя в репозиторий
        $user = User::createFrom(
            $username,
            $arguments->get('password'),
            new Name(
                $arguments->get('first_name'),
                $arguments->get('last_name')
            )
        );

        // Сохраняем пользователя в репозиторий
        $this->usersRepository->save($user);

        // Логируем информацию о новом пользователе
        $this->logger->info("User created: " . $user->uuid());
    }

    private function userExists(string $username): bool
    {
        try {
            // Пытаемся получить пользователя из репозитория
            $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException) {
            return false;
        }
        return true;
    }
}