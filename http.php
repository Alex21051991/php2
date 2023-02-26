<?php

use App\Blog\Exceptions\AppException;
use App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Http\Actions\Auth\LogIn;
use App\Http\Actions\Auth\LogOut;
use App\Http\Actions\Likes\CreateCommentLike;
use App\Http\Actions\Posts\CreatePost;
use App\Http\Actions\Likes\CreatePostLike;
use App\Http\Actions\Users\CreateUser;
use App\Http\Actions\Users\FindByUsername;
use App\Http\ErrorResponse;
use App\Http\SuccessfulResponse;
use App\Http\Request;
use App\Http\Actions\Posts\DeletePost;
use Psr\Log\LoggerInterface;

$container = require __DIR__ . '/bootstrap.php';

$logger = $container->get(LoggerInterface::class);

$request = new Request(
    $_GET,
    $_SERVER,
    file_get_contents('php://input'),
);

// Получаем объект логгера из контейнера
$logger = $container->get(LoggerInterface::class);

try {
    $path = $request->path();
} catch (HttpException $e) {
    // Логируем сообщение с уровнем WARNING
    $logger->warning($e->getMessage());
    (new ErrorResponse)->send();
    return;
}

try {
    $method = $request->method();
} catch (HttpException $e) {
    // Логируем сообщение с уровнем WARNING
    $logger->warning($e->getMessage());
    (new ErrorResponse)->send();
    return;
}

$routes = [
    'GET' => [
        '/users/show' => FindByUsername::class,
        //'/posts/show' => FindByUuid::class,
    ],
    'POST' => [
        '/login' => LogIn::class,
        '/logout' => LogOut::class,
        '/users/create' => CreateUser::class,
        '/posts/create' => CreatePost::class,
        '/likes/create' => CreatePostLike::class,
        '/likesComm/create' => CreateCommentLike::class,

    ],
    'DELETE' => [
        '/posts' => DeletePost::class,
    ],
];

if (!array_key_exists($method, $routes) || !array_key_exists($path, $routes[$method])) {
    $message = "Route not found: $method $path";
    (new ErrorResponse($message))->send();
    return;
}

$actionClassName = $routes[$method][$path];

$action = $container->get($actionClassName);

try {
    $action = $container->get($actionClassName);
    $response = $action->handle($request);
} catch (AppException $e) {
    // Логируем сообщение с уровнем ERROR
    $logger->error($e->getMessage(), ['exception' => $e]);
    // Больше не отправляем пользователю конкретное сообщение об ошибке, а только логируем его
    (new ErrorResponse($e->getMessage()))->send();
}

$response->send();