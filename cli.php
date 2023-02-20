<?php

use App\Blog\Commands\CreateUserCommand;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Exceptions\InvalidArgumentException;
use \App\Blog\Commands\Arguments;
use App\Blog\User;
use App\Person\Name;
use App\Blog\UUID;

require_once __DIR__ . '/vendor/autoload.php';

$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

$usersRepository = new SqliteUsersRepository($connection);

//$usersRepository->save(new User(UUID::random(), new Name('Anna', 'Petrova'), 'login'));
$command = new CreateUserCommand($usersRepository);

try {
    //$usersRepository->save(new User(UUID::random(), new Name('Ivan', 'Nikitin'), "admin"));
    //echo $usersRepository->getByUsername('admin');
    $command->handle(Arguments::fromArgv($argv));
} catch (Exception $e) {
    echo $e->getMessage();
}


/*
$faker = Faker\Factory::create('ru_RU');
//echo $faker->name() . PHP_EOL;
//echo $faker->realText(rand(100,200)) . PHP_EOL;
$name = new Name(
    $faker->firstName('male'),
    $faker->lastName()
);

$user = new User(
    $faker->randomDigitNotNull(),
    $name,
    $faker->sentence(1)
);
*/

