<?php

namespace App\Http\Actions\Auth;

use DateTimeImmutable;
use App\Blog\AuthToken;
use App\Blog\Exceptions\AuthException;
use App\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use App\Http\Actions\ActionInterface;
use App\Http\Auth\PasswordAuthenticationInterface;
use App\http\Request;
use App\Http\ErrorResponse;
use App\http\Response;
use App\Http\SuccessfulResponse;
use Exception;

class LogIn implements ActionInterface
{
    public function __construct(
        // Авторизация по паролю
        private PasswordAuthenticationInterface $passwordAuthentication,
        // Репозиторий токенов
        private AuthTokensRepositoryInterface $authTokensRepository
    ) {
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function handle(Request $request): Response
    {
        // Аутентифицируем пользователя
        try {
            $user = $this->passwordAuthentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        // Генерируем токен
        $authToken = new AuthToken(

            // Случайная строка длиной 40 символов
            bin2hex(random_bytes(40)),
            $user->uuid(),

            // Срок годности - 1 день
            (new DateTimeImmutable())->modify('+1 day')
        );

        // Сохраняем токен в репозиторий
        $this->authTokensRepository->save($authToken);

        // Возвращаем токен
        return new SuccessfulResponse([
            'token' => $authToken->token(),
        ]);

    }
}