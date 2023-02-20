<?php

use App\Blog\Container\DIContainer;
use App\Blog\Repositories\LikesCommentsRepository\LikesCommentsRepositoryInterface;
use App\Blog\Repositories\LikesCommentsRepository\SqliteLikesCommentsRepository;
use App\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use App\Blog\Repositories\LikesRepository\SqliteLikesRepository;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;

require_once __DIR__ . '/vendor/autoload.php';

$container = new DIContainer();

$container->bind(
    PDO::class,
    new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
);

$container->bind(
    LikesCommentsRepositoryInterface::class,
    SqliteLikesCommentsRepository::class
);

$container->bind(
    LikesRepositoryInterface::class,
    SqliteLikesRepository::class
);

$container->bind(
    PostsRepositoryInterface::class,
    SqlitePostsRepository::class
);

$container->bind(
    UsersRepositoryInterface::class,
    SqliteUsersRepository::class
);

return $container;
