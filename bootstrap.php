<?php

use App\Blog\Container\DIContainer;
use App\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use App\Blog\Repositories\AuthTokensRepository\SqliteAuthTokensRepository;
use App\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use App\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use App\Blog\Repositories\LikesCommentsRepository\LikesCommentsRepositoryInterface;
use App\Blog\Repositories\LikesCommentsRepository\SqliteLikesCommentsRepository;
use App\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use App\Blog\Repositories\LikesRepository\SqliteLikesRepository;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Http\Auth\AuthenticationInterface;
use App\Http\Auth\BearerTokenAuthentication;
use App\Http\Auth\IdentificationInterface;
use App\Http\Auth\JsonBodyUsernameIdentification;
use App\Http\Auth\PasswordAuthentication;
use App\Http\Auth\PasswordAuthenticationInterface;
use App\Http\Auth\TokenAuthenticationInterface;
use Dotenv\Dotenv;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/vendor/autoload.php';

// Загружаем переменные окружения из файла .env
Dotenv::createImmutable(__DIR__)->safeLoad();

$container = new DIContainer();

$container->bind(
    PDO::class,
    //new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
    new PDO('sqlite:' . __DIR__ . '/' . $_ENV['SQLITE_DB_PATH'])
);

// Выносим объект логгера в переменную
$logger = (new Logger('blog'));

if ('yes' === $_ENV['LOG_TO_FILES']) {
    $logger->pushHandler(new StreamHandler(
        __DIR__ . '/logs/blog.log'
    ))
        ->pushHandler(new StreamHandler(
            __DIR__ . '/logs/blog.error.log',
            level: Logger::ERROR,
            bubble: false,
        ));
}

if ('yes' === $_ENV['LOG_TO_CONSOLE']) {
    $logger->pushHandler(
        new StreamHandler("php://stdout")
    );
}

$container->bind(
    PasswordAuthenticationInterface::class,
    PasswordAuthentication::class
);

$container->bind(
    TokenAuthenticationInterface::class,
    BearerTokenAuthentication::class
);

$container->bind(
    AuthenticationInterface::class,
    PasswordAuthentication::class
);

$container->bind(
    AuthTokensRepositoryInterface::class,
    SqliteAuthTokensRepository::class
);

$container->bind(
    IdentificationInterface::class,
    JsonBodyUsernameIdentification::class
);

$container->bind(
    LoggerInterface::class,
    $logger
);

/*
// Добавляем логгер в контейнер
$container->bind(
    LoggerInterface::class,
    // .. ассоциируем объект логгера из библиотеки monolog
    (new Logger('blog')) // blog – это (произвольное) имя логгера
        // Настраиваем логгер так, чтобы записи сохранялись в файл
        ->pushHandler(new StreamHandler(
            __DIR__ . '/logs/blog.log' // Путь до этого файла
        ))
        ->pushHandler(new StreamHandler(
            // записывать в файл "blog.error.log"
            __DIR__ . '/logs/blog.error.log',
            // события с уровнем ERROR и выше,
            level: Logger::ERROR,
            // при этом событие не должно "всплывать"
            bubble: false,
        ))
        // Добавили ещё один обработчик; он будет вызываться первым …
        ->pushHandler(
            // .. и вести запись в поток php://stdout, то есть в консоль
            new StreamHandler("php://stdout")
        )
);
*/

$container->bind(
    LikesCommentsRepositoryInterface::class,
    SqliteLikesCommentsRepository::class
);

$container->bind(
    CommentsRepositoryInterface::class,
    SqliteCommentsRepository::class
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
