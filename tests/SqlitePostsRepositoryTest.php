<?php

use App\Blog\User;
use App\Blog\UUID;
use App\Blog\Post;
use App\Person\Name;
use \App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use \App\Blog\Exceptions\PostNotFoundException;
use \App\Blog\Exceptions\InvalidArgumentException;
//Use PDO;
//use PDOStatement;
use \PHPUnit\Framework\TestCase;

class SqlitePostsRepositoryTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testItThrowsAnExceptionWhenPostNotFound(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionMock->method('prepare')->willReturn($statementStub);

        $repository = new SqlitePostsRepository($connectionMock);

        $this->expectExceptionMessage('Cannot find user: a7bfa0e1-e276-408f-b086-4a6d5d3d8fe7');
        $this->expectException(PostNotFoundException::class);
        $repository->get(new UUID('a7bfa0e1-e276-408f-b086-4a6d5d3d8fe7'));
    }

    public function testItSavePostToDatabase(): void
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

        //
        $repository = new SqlitePostsRepository($connectionStub);

        $user = new User (
          new UUID('123e4567-e89b-12d3-a456-426614174000'),
            new Name('first_name', 'last_name'),
          'name',

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
     * @throws InvalidArgumentException
     */
    public function testItGetPostByUuid(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
           'uuid' => '95267850-37ff-4277-9b80-511cc5d48905',
            'author_uuid' => '95267850-37ff-4277-9b80-511cc5d48905',
            'title' => 'Заголовок',
            'text' => 'Текст для примера',
            'username' => 'ivan22',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
        ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $postRepository = new SqlitePostsRepository($connectionStub);
        $post = $postRepository->get(new UUID('95267850-37ff-4277-9b80-511cc5d48905'));

        $this->assertSame('95267850-37ff-4277-9b80-511cc5d48905', (string)$post->getUuid());

    }
}