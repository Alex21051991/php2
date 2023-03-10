<?php

namespace App\Http\Auth;

use App\Blog\Exceptions\AuthException;
use App\Blog\Exceptions\HttpException;
use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\User;
use App\Blog\UUID;
use App\Http\Request;

class JsonBodyUuidIdentification implements IdentificationInterface
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository
    )
    {
    }

    /**
     * @param Request $request
     * @return User
     * @throws AuthException
     */
     public function user(Request $request): User
     {
         try {
             // Получаем UUID пользователя из JSON-тела запроса;
             // ожидаем, что корректный UUID находится в поле user_uuid
             $userUuid = new UUID($request->jsonBodyField('user_uuid'));
         } catch (HttpException|InvalidArgumentException $e) {
             // Если невозможно получить UUID из запроса - бросаем исключение
             throw new AuthException($e->getMessage());
         }

         try {
             // Ищем пользователя в репозитории и возвращаем его
             return $this->usersRepository->get($userUuid);
         } catch (UserNotFoundException $e) {
             // Если пользователь с таким UUID не найден - бросаем исключение
             throw new AuthException($e->getMessage());
         }
     }
}