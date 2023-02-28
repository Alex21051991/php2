<?php

namespace App\Blog\Repositories\LikesRepository;

use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\LikeAlreadyExists;
use App\Blog\Exceptions\LikesNotFoundException;
use App\Blog\Like;
use App\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use App\Blog\UUID;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(Like $like): void
    {
        $statement = $this->connection->prepare('
            INSERT INTO likes_comments (uuid, user_uuid, comment_uuid)
            VALUES (:uuid, :user_uuid, :post_uuid)
        ');
        $statement->execute([
            ':uuid' => (string)$like->uuid(),
            ':user_uuid' => (string)$like->getUserId(),
            ':comment_uuid' => (string)$like->getPostId(),
        ]);
    }

    /**
     * @throws LikesNotFoundException
     * @throws InvalidArgumentException
     */
    public function getByPostUuid(UUID $uuid): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes_comments WHERE comment_uuid = :uuid'
        );

        $statement->execute([
            'uuid' => (string)$uuid
        ]);

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!$result) {
            throw new LikesNotFoundException(
                'No likes to comment with uuid = : ' . $uuid
            );
        }

        $likes = [];
        foreach ($result as $like) {
            $likes[] = new Like(
                uuid: new UUID($like['uuid']),
                post_id: new UUID($like['post_uuid']),
                user_id: new UUID($like['user_uuid']),
            );
        }
        return $likes;
    }

    /**
     * @throws LikeAlreadyExists
     */
    public function checkUserLikeForPostExists($commentUuid, $userUuid): void
    {
        $statement = $this->connection->prepare(
            'SELECT *  FROM likes_comments
            WHERE comment_uuid = :commentUuid AND user_uuid = :userUuid'
        );

        $statement->execute([
            ':commentUuid' => $commentUuid,
            ':userUuid' => $userUuid
        ]);

        $isExisted = $statement->fetch();

        if ($isExisted) {
            throw new LikeAlreadyExists(
                'The users like for this comment already exists'
            );
        }
    }
}