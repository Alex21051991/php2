<?php

use App\Blog\Commands\Arguments;
use App\Blog\Commands\CreateUserCommand;

$container = require __DIR__ . '/bootstrap.php';

try {

    // При помощи контейнера создаём команду
    $command = $container->get(CreateUserCommand::class);
    $command->handle(Arguments::fromArgv($argv));

} catch (Exception $e) {
    echo $e->getMessage();
}
