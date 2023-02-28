<?php

namespace lite\Blog\UnitTests\Commands;

//use App\Blog\Commands\Arguments;
use App\Blog\Commands\Users\CreateUser;
//use App\Blog\Exceptions\ArgumentsException;
//use App\Blog\Exceptions\CommandException;
//use App\Blog\Exceptions\InvalidArgumentException;

use App\Blog\Exceptions\UserNotFoundException;
//use App\Blog\Repositories\UsersRepository\DummyUsersRepository;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\User;
use App\Blog\UUID;
//use lite\Blog\UnitTests\DummyLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CreateUserCommandTest extends TestCase
{
    public function testItRequiresPassword(): void
    {
        $command = new CreateUser($this->makeUsersRepository());
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "first_name, last_name, password"');
        $command->run(new ArrayInput([
            'username' => 'Ivan',
        ]),
            new NullOutput()
        );
    }
/*
    public function testItThrowsAnExceptionWhenUserAlreadyExists(): void
    {
        $command = new CreateUser(new DummyUsersRepository(), new DummyLogger());
        // Описываем тип ожидаемого исключения
        $this->expectException(CommandException::class);

        // и его сообщение
        $this->expectExceptionMessage('User already exists: Ivan');

        // Запускаем команду с аргументами
        $command->handle(new Arguments(['username' => 'Ivan']));
    }
*/
    // Тест проверяет, что команда действительно требует имя пользователя
    public function testItRequiresFirstName(): void
    {
        // Передаём объект анонимного класса в качестве реализации UsersRepositoryInterface
        $command = new CreateUser($this->makeUsersRepository());
        // Ожидаем, что будет брошено исключение
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "first_name, last_name").');
        // Запускаем команду
        $command->run(new ArrayInput([
            'username' => 'Ivan', 'password' => 'some_password'
        ]),
            new NullOutput()
        );
    }

    // Тест проверяет, что команда действительно требует фамилию пользователя
    public function testItRequiresLastName(): void
    {
        $command = new CreateUser($this->makeUsersRepository());
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "last_name").');

        $command->run(new ArrayInput([
            'username' => 'Ivan',
            'password' => 'some_password',
            'first_name' => 'Ivan',
        ]),
            // Передаём также объект, реализующий контракт OutputInterface Нам подойдёт реализация, которая ничего не делает
            new NullOutput()
        );
    }

    // Функция возвращает объект типа UsersRepositoryInterface
    private function makeUsersRepository(): UsersRepositoryInterface
    {
        return new class implements UsersRepositoryInterface {
            public function save(User $user): void
            {
            }

            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User

            {
                throw new UserNotFoundException("Not found");
            }
        };
    }

    // Тест, проверяющий, что команда сохраняет пользователя в репозитории

    /**
     * @return void
     * @throws ExceptionInterface
     */
    public function testItSavesUserToRepository(): void
    {
        // Создаём объект анонимного класса
        $usersRepository = new class implements UsersRepositoryInterface {
            // В этом свойстве мы храним информацию о том, был ли вызван метод save
            private bool $called = false;

            public function save(User $user): void
            {
                // Запоминаем, что метод save был вызван
                $this->called = true;
            }

            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
            // Этого метода нет в контракте UsersRepositoryInterface, но ничто не мешает его добавить.
            // С помощью этого метода мы можем узнать, был ли вызван метод save
            public function wasCalled(): bool
            {
                return $this->called;
            }
        };

        $command = new CreateUser($usersRepository);

        // Запускаем команду
        $command->run(new ArrayInput([
            'username' => 'Ivan',
            'password' => '123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
        ]),
        new NullOutput()
        );

        $this->assertTrue($usersRepository->wasCalled());
    }
}