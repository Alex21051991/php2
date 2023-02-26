<?php

namespace App\Http\Actions\Auth;

use App\Blog\Exceptions\AuthException;
use App\Http\Actions\ActionInterface;
use App\Http\Auth\TokenAuthenticationInterface;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Http\Response;
use App\Http\SuccessfulResponse;

class LogOut implements ActionInterface
{
    public  function __construct(
        private TokenAuthenticationInterface $authentication
    ){}

    public function handle(Request $request): Response
    {
        try {
            $this->authentication->logout($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfulResponse();
    }
}
