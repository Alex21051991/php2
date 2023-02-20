<?php

namespace App\Http\Actions\Posts;

use App\Blog\Exceptions\InvalidArgumentException;
use App\Http\Actions\ActionInterface;
use App\Http\ErrorResponse;
use App\Blog\Exceptions\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Http\SuccessfulResponse;
use App\Blog\Post;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\UUID;

class CreatePost implements ActionInterface
{
    // Внедряем репозитории статей и пользователей
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
        private UsersRepositoryInterface $usersRepository,
    ) {
    }

    public function handle(Request $request): Response
    {
        // Пытаемся создать UUID пользователя из данных запроса
        try {
            $authorUuid = new UUID($request->jsonBodyField('author_uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }

        // Пытаемся найти пользователя в репозитории
        try {
            $user = $this->usersRepository->get($authorUuid);
        } catch (UserNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        // Генерируем UUID для новой статьи
        $newPostUuid = UUID::random();
        try {
            // Пытаемся создать объект статьи из данных запроса
            $post = new Post(
                $newPostUuid,
                $user,
                $request->jsonBodyField('title'),
                $request->jsonBodyField('text'),
            );
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        // Сохраняем новую статью в репозитории
        $this->postsRepository->save($post);

        // Возвращаем успешный ответ, содержащий UUID новой статьи
        return new SuccessfulResponse([
            'uuid' => (string)$newPostUuid,
        ]);
    }
}