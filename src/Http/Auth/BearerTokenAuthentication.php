<?php

namespace App\Http\Auth;

use App\Blog\AuthToken;
use App\Blog\Exceptions\AuthTokensRepositoryException;
use DateTimeImmutable;
use App\Blog\Exceptions\AuthException;
use App\Blog\Exceptions\AuthTokenNotFoundException;
use App\Blog\Exceptions\HttpException;
use App\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\User;
use App\Http\Request;

class BearerTokenAuthentication implements TokenAuthenticationInterface
{

    private const HEADER_PREFIX = 'Bearer ';

    public function __construct(
		// Репозиторий токенов
        private AuthTokensRepositoryInterface $authTokensRepository,
		// Репозиторий пользователей
        private UsersRepositoryInterface $usersRepository,
    ) {
    }

    /**
     * @param Request $request
     * @return void
     * @throws AuthException|AuthTokensRepositoryException
     */
    public function logout(Request $request): void
    {
        $authToken = $this->getAuthToken($request);
        $this->authTokensRepository->delete($authToken->token());
    }

    private function getAuthToken(Request $request): AuthToken
    {
        // Получаем HTTP-заголовок
        try {
            $header = $request->header('Authorization');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }

        // Проверяем, что заголовок имеет правильный формат
        if (!str_starts_with($header, self::HEADER_PREFIX)) {
            throw new AuthException("Malformed token: [$header]");
        }

        // Отрезаем префикс Bearer
        $token = mb_substr($header, strlen(self::HEADER_PREFIX));

        // Ищем токен в репозитории
        try {
            $authToken = $this->authTokensRepository->get($token);
        } catch (AuthTokenNotFoundException) {
            throw new AuthException("Bad token: [$token]");
        }

        // Проверяем срок годности токена
        if ($authToken->expiresOn() <= new DateTimeImmutable()) {
            throw new AuthException("Token expired: [$token]");
        }

        return $authToken;
    }

    /**
     * @throws AuthException
     */
    public function user(Request $request): User
    {
        $authToken = $this->getAuthToken($request);

		// Получаем UUID пользователя из токена
        $userUuid = $authToken->userUuid();

		// Ищем и возвращаем пользователя
        return $this->usersRepository->get($userUuid);
    }
}