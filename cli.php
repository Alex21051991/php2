<?php

use App\Blog\Commands\Arguments;
use App\Blog\Commands\CreateUserCommand;
use App\Blog\Exceptions\AppException;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\User;
use Psr\Log\LoggerInterface;

$container = require __DIR__ . '/bootstrap.php';

// При помощи контейнера создаём команду
$command = $container->get(CreateUserCommand::class);

// Получаем объект логгера из контейнера
$logger = $container->get(LoggerInterface::class);

try {
    $command->handle(Arguments::fromArgv($argv));
} catch (AppException $e) {
    //echo $e->getMessage();
    // Уровень логирования – ERROR
    $logger->error($e->getMessage(), ['exception' => $e]);
}

$command2 = $container->get(SqliteUsersRepository::class);

try {
    $command2->save(User::fromArgv($argv));
} catch (AppException $e) {
    //echo $e->getMessage();
    // Уровень логирования – ERROR
    $logger->error($e->getMessage(), ['exception' => $e]);
}
