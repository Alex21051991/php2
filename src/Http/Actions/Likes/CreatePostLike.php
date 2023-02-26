<?php

namespace App\Http\Actions\Likes;

use App\Blog\Exceptions\AuthException;
use App\Blog\Exceptions\HttpException;
use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\LikeAlreadyExists;
use App\Blog\Exceptions\PostNotFoundException;
use App\Blog\Like;
use App\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\UUID;
use App\Http\Actions\ActionInterface;
use App\Http\Auth\TokenAuthenticationInterface;
use App\Http\ErrorResponse;
use App\http\Request;
use App\http\Response;
use App\Http\SuccessfulResponse;

class CreatePostLike implements ActionInterface
{
    public   function __construct(
        private LikesRepositoryInterface $likesRepository,
        private PostsRepositoryInterface $postRepository,
        //private UsersRepositoryInterface $usersRepository,
        private TokenAuthenticationInterface $authentication,
    )
    {
    }

    /**
     * @param Request $request
     * @return Response
     * @throws InvalidArgumentException
     * @throws \JsonException
     */
    public function handle(Request $request): Response
    {
        try {
            $postUuid = new UUID($request->JsonBodyField('post_uuid'));
            //$userUuid = $request->JsonBodyField('user_uuid');
            $userUuid = $this->authentication->user($request);
        } catch (HttpException $e) {
           return new ErrorResponse($e->getMessage());
        }

        try {
            $this->postRepository->get(new UUID($postUuid));
        } catch (PostNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $this->likesRepository->checkUserLikeForPostExists($postUuid, $userUuid);
        } catch (LikeAlreadyExists $e) {
            return new ErrorResponse($e->getMessage());
        }

        $newLikeUuid = UUID::random();

        $like = new Like(
            uuid: $newLikeUuid,
            post_comm_id: new UUID($postUuid),
            user_id: new UUID($userUuid),
        );

        $this->likesRepository->save($like);

        return new SuccessFulResponse(
            ['uuid' => (string)$newLikeUuid]
        );
    }
}