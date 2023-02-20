<?php

use App\Blog\User;
use App\Blog\UUID;
use App\Person\Name;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;

require_once __DIR__ . '/vendor/autoload.php';

$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

$usersRepository = new SqliteUsersRepository($connection);

try {
    $user = $usersRepository->save(new User(UUID::random(), new Name('Ivan', 'Nikitin'), 'login'));
    var_dump($user);
} catch (Exception $e) {
    echo $e;
}

