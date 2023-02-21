<?php

namespace App\Http\Actions\Likes;

use App\Blog\Exceptions\CommentNotFoundException;
use App\Blog\Exceptions\HttpException;
use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\LikeAlreadyExists;
use App\Blog\Like;
use App\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use App\Blog\Repositories\LikesCommentsRepository\LikesCommentsRepositoryInterface;
use App\Blog\UUID;
use App\Http\Actions\ActionInterface;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Http\Response;
use App\Http\SuccessfulResponse;

class CreateCommentLike implements ActionInterface
{
    public   function __construct(
        private LikesCommentsRepositoryInterface $likesCommentRepository,
        private CommentsRepositoryInterface $commentRepository,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle(Request $request): Response
    {
        try {
            $commentUuid = $request->JsonBodyField('comment_uuid');
            $userUuid = $request->JsonBodyField('user_uuid');
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        //TODO тоже и для юзера добавить
        try {
            $this->commentRepository->get(new UUID($commentUuid));
        } catch (CommentNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $this->likesCommentRepository->checkUserLikeForCommentExists($commentUuid, $userUuid);
        } catch (LikeAlreadyExists $e) {
            return new ErrorResponse($e->getMessage());
        }

        $newLikeUuid = UUID::random();

        $like = new Like(
            uuid: $newLikeUuid,
            post_comm_id: new UUID($commentUuid),
            user_id: new UUID($userUuid),
        );

        $this->likesCommentRepository->save($like);

        return new SuccessFulResponse(
            ['uuid' => (string)$newLikeUuid]
        );
    }


}