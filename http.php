<?php

/*
 POST http://localhost:80/likesComm/create
{
"post_uuid": "a7bfa0e1-e276-408f-b086-4a6d5d3d8fe7",
"user_uuid": "a7bfa0e1-e276-408f-b086-4a6d5d3d8fe6"
}

/posts/create
"author_uuid": "26f8f204-baa9-434b-8114-0e89254baf8d",
"title":"new Tittle",
"text":"new text"
 */

use App\Blog\Exceptions\AppException;
use App\Http\Actions\Likes\CreateCommentLike;
use App\Http\Actions\Posts\CreatePost;
use App\Http\Actions\Likes\CreatePostLike;
use App\Http\Actions\Users\CreateUser;
use App\Http\Actions\Users\FindByUsername;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Http\Actions\Posts\DeletePost;
use Psr\Log\LoggerInterface;

$container = require __DIR__ . '/bootstrap.php';

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
    ],
    'POST' => [
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
