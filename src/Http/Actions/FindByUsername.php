<?php

namespace App\Http\Actions;

use App\Blog\Exceptions\HttpException;
use App\Blog\Exceptions\UserNotFoundException;
use App\Http\SuccessfulResponse;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Http\ErrorResponse;
use App\Http\Request;


class FindByUsername implements ActionInterface
{
    // Нам понадобится репозиторий пользователей,
    // внедряем его контракт в качестве зависимост
    public function __construct(
    private UsersRepositoryInterface $usersRepository
    )
    {
    }

    // Функция, описанная в контракте
    public function handle(Request $request): ErrorResponse
    {
        try {
            // Пытаемся получить искомое имя пользователя из запроса
            $username = $request->query('username');
        } catch (HttpException $e) {
            // Если в запросе нет параметра username -
            // возвращаем неуспешный ответ,
            // сообщение об ошибке берём из описания исключения
            return new ErrorResponse($e->getMessage());
        }
        try {
            // Пытаемся найти пользователя в репозитории
            $user = $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $e) {
            // Если пользователь не найден -
            // возвращаем неуспешный ответ
            return new ErrorResponse($e->getMessage());
        }

        // Возвращаем успешный ответ
        return new SuccessfulResponse([
            'username' => $user->username(),
            'name' => $user->name()->first() . ' ' . $user->name()->last(),
        ]);
    }

}