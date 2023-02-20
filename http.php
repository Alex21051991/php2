<?php

/*
 POST http://127.0.0.1:80/posts/create
{
"author_uuid": "a7bfa0e1-e276-408f-b086-4a6d5d3d8fe6",
"text": "some text",
"title": "some title"
}

DELETE http://127.0.0.1:80/posts?uuid=69c0f45a-70b7-4324-9622-eb9abde6bf3c
 */



use App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Http\Actions\Posts\CreatePost;
use App\Http\Actions\Users\CreateUser;
use App\Http\Actions\Users\FindByUsername;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Http\Actions\Posts\DeletePost;


require_once __DIR__ . '/vendor/autoload.php';

$request = new Request($_GET, $_SERVER, file_get_contents('php://input'),);

try {
    // Пытаемся получить HTTP-метод запроса
    $method = $request->method();
} catch (HttpException) {
    // Возвращаем неудачный ответ,
    // если по какой-то причине
    // не можем получить метод
    (new ErrorResponse)->send();
    return;
}

$routes = [
    // Добавили ещё один уровень вложенности
    // для отделения маршрутов,
    // применяемых к запросам с разными методами
    'GET' => [
        '/users/show' => new FindByUsername(
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
    ],
    'POST' => [
        '/users/create' => new CreateUser(
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
        '/posts/create' => new CreatePost(
            new SqlitePostsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            ),
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
    ],

    'DELETE' => [
      '/posts' => new DeletePost(
          new SqlitePostsRepository(
              new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
          )
      ),
    ],

];

try {
    $path = $request->path();
} catch (HttpException) {
    (new ErrorResponse)->send();
    return;
}

try {
    $method = $request->method();
} catch (HttpException) {
    (new ErrorResponse)->send();
    return;
}



// Если у нас нет маршрутов для метода запроса -
// возвращаем неуспешный ответ
if (!array_key_exists($method, $routes)) {
    (new ErrorResponse('Not found'))->send();
    return;
}

// Ищем маршрут среди маршрутов для этого метода
if (!array_key_exists($path, $routes[$method])) {
    (new ErrorResponse('Not found'))->send();
    return;
}

// Выбираем действие по методу и пути
$action = $routes[$method][$path];

try {
    $response = $action->handle($request);
    $response->send();
} catch (Exception $e) {
    (new ErrorResponse($e->getMessage()))->send();
}




/*
// Получаем данные из объекта запроса
$parameter = $request->query('some_parameter');
$header = $request->header('Some-Header');
$path = $request->path();

// Создаём объект ответа
$response = new SuccessfulResponse([
    'message' => 'Hello from PHP',
]);

// Отправляем ответ
$response->send();
*/