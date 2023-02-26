<?php

namespace App\Http\Actions\Posts;

use App\Blog\Exceptions\AuthException;
use App\Blog\Exceptions\InvalidArgumentException;
//use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Post;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\UUID;
use App\Blog\Exceptions\HttpException;
use App\http\Actions\ActionInterface;
use App\Http\Auth\AuthenticationInterface;
use App\Http\Auth\IdentificationInterface;
use App\Http\Auth\JsonBodyUsernameIdentification;
use App\Http\Auth\TokenAuthenticationInterface;
use App\http\ErrorResponse;
use App\http\Request;
use App\http\Response;
use App\http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class CreatePost implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
        private LoggerInterface $logger,
        private TokenAuthenticationInterface $authentication,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle(Request $request): Response
    {
        try {
            $user = $this->authentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $newPostUuid = UUID::random();

        try {
            $post = new Post(
                $newPostUuid,
                $user,
                $request->jsonBodyField('title'),
                $request->jsonBodyField('text'),
            );
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        $this->postsRepository->save($post);
        $this->logger->info("Post created: $newPostUuid");

        return new SuccessfulResponse([
            'uuid' => (string)$newPostUuid,
        ]);
    }
}