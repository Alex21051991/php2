<?php

namespace lite\Blog\UnitTests;

use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\PostNotFoundException;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Post;
use App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use App\Blog\User;
use App\Blog\UUID;
use App\Person\Name;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqlitePostsRepositoryTest extends TestCase
{
    public function testItThrowsAnExceptionWhenPostNotFound(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionMock->method('prepare')->willReturn($statementStub);

        $repository = new SqlitePostsRepository($connectionMock, new DummyLogger());

        $this->expectExceptionMessage('Cannot find post: d02eef61-1a06-460f-b859-202b84164734');
        $this->expectException(PostNotFoundException::class);
        $repository->get(new UUID('d02eef61-1a06-460f-b859-202b84164734'));
    }

    public function testItSavesPostToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once()) // Ожидаем, что будет вызван один раз
            ->method('execute') // метод execute
            ->with([ // с единственным аргументом - массивом
                ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ':author_uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ':title' => 'Ivan',
                ':text' => 'Nikitin',
            ]);

        $connectionStub->method('prepare')->willReturn($statementMock);
        $repository = new SqlitePostsRepository($connectionStub, new DummyLogger());


        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            new Name('first_name', 'last_name'),
            'name',
            'ivan',
        );

        $repository->save(
            new Post(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                $user,
                'Ivan',
                'Nikitin'
            )
        );
    }

    /**
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function testItGetPostByUuid(): void
    {
        $connectionStub = $this->createStub(\PDO::class);
        $statementMock = $this->createMock(\PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
            'uuid' => '7b094211-1881-40f4-ac73-365ad0b2b2d4',
            'author_uuid' => '5a91ed7a-0ae4-495f-b666-c52bc8f13fe4',
            'title' => 'Заголовок',
            'text' => 'Какой-то текст',
            'username' => 'ivan123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
            'password' => 'some password',
        ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $postRepository = new SqlitePostsRepository($connectionStub, new DummyLogger());
        $post = $postRepository->get(new UUID('7b094211-1881-40f4-ac73-365ad0b2b2d4'));

        $this->assertSame('7b094211-1881-40f4-ac73-365ad0b2b2d4', (string)$post->uuid());
    }
}