<?php

namespace App\Blog\Repositories\CommentsRepository;

use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\Comment;
use App\Blog\Post;
use App\Blog\UUID;
use Exception;
use PDO;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{
    private PDO $connection;

    public function __construct(\PDO $connection) {
        $this->connection = $connection;
    }

    public function save(Comment $comment): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO comments (uuid, post_uuid, author_uuid, text) VALUES (:uuid, :post_uuid, :author_uuid, :text');

        $statement->execute([
            ':uuid' => $comment->getUuid(),
            ':post_uuid' => $comment->getPost()->getUuid(),
            ':author_uuid' => $comment->getUser()->uuid(),
            ':text' => $comment->getText(),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM comments WHERE uuid = :uuid'
        );
        $statement->execute([
            'uuid' => (string)$uuid,
        ]);

        return $this->getComment($statement, $uuid);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    private function getComment(\PDOStatement $statement, string $commentUuId): Comment
    {
            $result = $statement->fetch(\PDO::FETCH_ASSOC);

            if ($result === false) {
                throw new Exception("Cannot find post: $commentUuId");
                //echo "Cannot find post: $postUuId";
            }

            // print_r($result);
            //die();

            $userRepository = new SqliteUsersRepository($this->connection);
            $post = $userRepository->get(new UUID($result['post_uuid']));
            $user = $userRepository->get(new UUID($result['author_uuid']));

            return new Comment(
                new UUID($result['uuid']),
                $post,
                $user,
                $result['text']
            );
        }
}