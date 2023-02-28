<?php

namespace App\Blog\Commands\Users;

use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\User;
use App\Blog\UUID;
use App\Person\Name;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUser extends Command
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('users:update')
            ->setDescription('Updates a user')
            ->addArgument(
                'uuid',
                InputArgument::REQUIRED,
                'UUID of a user to update'
            )
            ->addOption(
                // Имя опции
                'first-name',
                // Сокращённое имя
                'f',
                // Опция имеет значения
                InputOption::VALUE_OPTIONAL,
                // Описание
                'First name',
            )
            ->addOption(
                'last-name',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Last name',
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws InvalidArgumentException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        // Получаем значения опций
        $firstName = $input->getOption('first-name');
        $lastName = $input->getOption('last-name');

        // Выходим, если обе опции пусты
        if (empty($firstName) && empty($lastName)) {
            $output->writeln('Nothing to update');
            return Command::SUCCESS;
        }
        // Получаем UUID из аргумента
        $uuid = new UUID($input->getArgument('uuid'));

        // Получаем пользователя из репозитория
        $user = $this->usersRepository->get($uuid);

        // Создаём объект обновлённого имени
        $updatedName = new Name(
            // Берём сохранённое имя, если опция имени пуста
            firstName: empty($firstName)
                ? $user->name()->first() : $firstName,
            // Берём сохранённую фамилию, если опция фамилии пуста
            lastName: empty($lastName)
                ? $user->name()->last() : $lastName
        );

        // Создаём новый объект пользователя
        $updatedUser = new User(
            uuid: $uuid,
            name: $updatedName,
            username: $user->username(),
            hashedPassword: $user->hashedPassword()
        );

        // Сохраняем обновлённого пользователя
        $this->usersRepository->save($updatedUser);

        $output->writeln("User updated: $uuid");

        return Command::SUCCESS;
    }
}
