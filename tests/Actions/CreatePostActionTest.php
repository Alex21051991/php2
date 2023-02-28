<?php

namespace lite\Blog\UnitTests\Actions;

use App\Blog\Exceptions\AppException;
use App\Blog\Exceptions\AuthException;
use App\Blog\Exceptions\InvalidArgumentException;
use App\Http\Auth\IdentificationInterface;
use App\Http\Auth\JsonBodyUsernameIdentification;
use App\Http\Auth\JsonBodyUuidIdentification;
use App\Http\Auth\TokenAuthenticationInterface;
use App\Http\ErrorResponse;
use App\Blog\Exceptions\JsonException;
use App\Http\Actions\Posts\CreatePost;
use App\http\Request;
use App\http\SuccessfulResponse;
use App\Person\Name;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\User;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Post;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\UUID;
use App\Blog\Exceptions\PostNotFoundException;
use lite\Blog\UnitTests\DummyLogger;
use PHPUnit\Framework\TestCase;

class CreatePostActionTest extends TestCase
{
    private function postsRepository(): PostsRepositoryInterface
    {
        return new class() implements PostsRepositoryInterface {
            private bool $called = false;

            public function __construct()
            {
            }

            public function save(Post $post): void
            {
                $this->called = true;
            }

            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException('Not found');
            }

            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException('Not found');
            }

            public function getCalled(): bool
            {
                return $this->called;
            }

            public function delete(UUID $uuid): void
            {
            }
        };
    }

    /**
     * @param array $users
     * @return UsersRepositoryInterface
     */
    private function usersRepository(array $users): UsersRepositoryInterface
    {
        return new class($users) implements UsersRepositoryInterface
        {
            public function __construct(
                private array $users
            )
            {
            }

            public function save(User $user): void
            {
            }

            public function get(UUID $uuid): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && (string)$uuid == $user->uuid()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException('Cannot find user: ' . $uuid);
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException('Not found');
            }
        };
    }

    /**
     * @return void
     * @throws InvalidArgumentException|\JsonException
     */
    public function testItReturnsSuccessAnswer(): void
    {
        $postsRepositoryStub = $this->createStub(PostsRepositoryInterface::class);
        $authenticationStub = $this->createStub(TokenAuthenticationInterface::class);

        $authenticationStub
            ->method('user')
            ->willReturn(
                new User(
                    new UUID("10373537-0805-4d7a-830e-22b481b4859c"),
                    new Name('first', 'last'),
                    'username', 'password',
                )
            );

        $createPost = new CreatePost($postsRepositoryStub,  new DummyLogger(), $authenticationStub);

        $request = new Request(
            [],
            [],
            '{
                "title": "lorem",
                "text": "lorem"
                }'
        );

        $actual = $createPost->handle($request);

        $this->assertInstanceOf(
            SuccessFulResponse::class,
            $actual
        );
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws \JsonException
     */

    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([], [], '{"author_uuid":"10373537-0805-4d7a-830e-22b481b4859c","title":"title","text":"text"}');

        $authenticationStub = $this->createStub(TokenAuthenticationInterface::class);
        $authenticationStub
            ->method('user')
            ->willReturn(
                new User(
                    new UUID("10373537-0805-4d7a-830e-22b481b4859c"),
                    new Name('first', 'last'),
                    'username','password',
                )
            );

        $postsRepository = $this->postsRepository();

        $action = new CreatePost($postsRepository, new DummyLogger(), $authenticationStub);

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);

        $this->setOutputCallback(function ($data) {
            $dataDecode = json_decode(
                $data,
                associative: true,
                flags: 0 //JSON_THROW_ON_ERROR
            );

            $dataDecode['data']['uuid'] = "351739ab-fc33-49ae-a62d-b606b7038c87";
            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        $this->expectOutputString('{"success":true,"data":{"uuid":"351739ab-fc33-49ae-a62d-b606b7038c87"}}');


        $response->send();
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws \JsonException
     */
    public function testItReturnsErrorResponseIfNotFoundUser(): void
    {
        $request = new Request([], [], '{"author_uuid":"10373537-0805-4d7a-830e-22b481b4859c","title":"title","text":"text"}');

        $postsRepositoryStub = $this->createStub(PostsRepositoryInterface::class);
        $authenticationStub = $this->createStub(TokenAuthenticationInterface::class);

        $authenticationStub
            ->method('user')
            ->willThrowException(
                new AuthException('Cannot find user: 10373537-0805-4d7a-830e-22b481b4859c')
            );

        $action = new CreatePost($postsRepositoryStub, new DummyLogger(), $authenticationStub);

        $response = $action->handle($request);

        $response->send();

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Cannot find user: 10373537-0805-4d7a-830e-22b481b4859c"}');
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws \JsonException
     */
    public function testItReturnsErrorResponseIfNoTextProvided(): void
    {
        $request = new Request([], [], '{"author_uuid":"10373537-0805-4d7a-830e-22b481b4859c","title":"title"}');

        $postsRepository = $this->postsRepository([]);
        $authenticationStub = $this->createStub(TokenAuthenticationInterface::class);
        $authenticationStub
            ->method('user')
            ->willReturn(
                new User(
                    new UUID("10373537-0805-4d7a-830e-22b481b4859c"),
                    new Name('first', 'last'),
                    'username',
                    'password',
                )
            );

        $action = new CreatePost($postsRepository, new DummyLogger(), $authenticationStub);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such field: text"}');

        $response->send();
    }

}