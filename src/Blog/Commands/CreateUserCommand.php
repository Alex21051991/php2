<?php

namespace App\Blog\Commands;

use App\Blog\Exceptions\ArgumentsException;
//use App\Blog\Exceptions\CommandException;
use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\User;
use App\Blog\UUID;
use App\Person\Name;
//use Psr\Log\LoggerInterface;

//php cli.php username=ivan first_name=Ivan last_name=Nikitin

class CreateUserCommand
{
    // Команда зависит от контракта репозитория пользователей, а не от конкретной реализации
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
        //private LoggerInterface $logger,
    )
    {
    }

    /**
     * @param Arguments $arguments
     * @return void
     * @throws ArgumentsException|InvalidArgumentException
     */
    public function handle(Arguments $arguments): void
    {
        //$this->logger->info("Create user command started");
        $username = $arguments->get('username');

        // Проверяем, существует ли пользователь в репозитории
        if ($this->userExists($username)) {
            // Логируем сообщение с уровнем WARNING
            //$this->logger->warning("User already exists: $username");
            // Вместо выбрасывания исключения просто выходим из функции
            return;
            // Бросаем исключение, если пользователь уже существует
            //throw new CommandException("User already exists: $username");
        }

        $uuid = UUID::random();

        // Сохраняем пользователя в репозиторий
        $this->usersRepository->save(new User(
            $uuid,
            new Name(
                $arguments->get('first_name'),
                $arguments->get('last_name')),
            $username,
        ));

        // Логируем информацию о новом пользователе
        //$this->logger->info("User created: $uuid");
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