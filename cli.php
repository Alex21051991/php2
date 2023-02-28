<?php

//use App\Blog\Commands\Arguments;
//use App\Blog\Commands\CreateUserCommand;
use App\Blog\Commands\FakeData\PopulateDB;
use App\Blog\Commands\Posts\DeletePost;
use App\Blog\Commands\Users\CreateUser;
use App\Blog\Commands\Users\UpdateUser;
//use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;

$container = require __DIR__ . '/bootstrap.php';

// Создаём объект приложения
$application = new Application();

// Перечисляем классы команд
$commandsClasses = [
    CreateUser::class,
    DeletePost::class,
    UpdateUser::class,
    PopulateDB::class,
];

foreach ($commandsClasses as $commandClass) {
    $command = $container->get($commandClass);
    $application->add($command);
}

// Запускаем приложение
$application->run();

/*
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
*/