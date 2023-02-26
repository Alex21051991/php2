<?php

use App\Blog\Commands\Arguments;
use App\Blog\Commands\CreateUserCommand;
//use App\Blog\Exceptions\AppException;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use App\Blog\User;
use App\Blog\UUID;
use Psr\Log\LoggerInterface;

$container = require __DIR__ . '/bootstrap.php';

// При помощи контейнера создаём команду


// Получаем объект логгера из контейнера
$logger = $container->get(LoggerInterface::class);

try {
	$command = $container->get(CreateUserCommand::class);
    $command->handle(Arguments::fromArgv($argv));
} catch (Exception $e) {
    //echo $e->getMessage();
    // Уровень логирования – ERROR
    $logger->error($e->getMessage(), ['exception' => $e]);
}