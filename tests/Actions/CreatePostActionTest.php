<?php

namespace App\Blog\tests\Actions;

use App\Blog\Exceptions\PostNotFoundException;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Post;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\User;
use App\Http\Actions\Posts\CreatePost;
use App\Http\ErrorResponse;
use App\Http\SuccessfulResponse;
use App\Person\Name;
use App\Http\Request;
use JsonException;
use PHPUnit\Framework\TestCase;
use App\Blog\UUID;

class CreatePostActionTest extends TestCase
{
    private  function postsRepository(): PostsRepositoryInterface
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
                throw new PostNotFoundException('Not found action get');
            }

            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException('Not found action getByTitle');
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
                throw new UserNotFoundException('Not found - getByUsername' );
            }

            public function delete(UUID $uuid): void
            {
            }
        };
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([], [], '{"author_uuid":"95267850-37ff-4277-9b80-511cc5d48905", "title":"title", "text":"text"}');

        $postsRepository = $this->postsRepository();

        $usersRepository = $this->usersRepository([
           new User(
               new UUID('95267850-37ff-4277-9b80-511cc5d48905'),
               new Name('first_name', 'last_name'),
               'username',
           )
        ]);

        $action = new CreatePost($usersRepository, $postsRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);

        $this->setOutputCallback(function ($data){
            $dataDecode = json_decode(
                $data,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            $dataDecode['data']['uuid'] = "a7bfa0e1-e276-408f-b086-4a6d5d3d8fe6";
            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        $this->expectOutputString('{"success":true, "data":{"a7bfa0e1-e276-408f-b086-4a6d5d3d8fe6"}}');

        $response->send();
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testReturnsErrorResponseIfNotFoundUser(): void
    {
        $request = new Request([],[],'{"author_uuid":"95267850-37ff-4277-9b80-511cc5d48905", "title":"title", "text":"text"}');

        $postRepository = $this->postsRepository();
        $userRepository = $this->usersRepository();

        $action = new CreatePost($userRepository, $postRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Cannot find user:95267850-37ff-4277-9b80-511cc5d48905"}');

        $response->send();
    }

    /**
     * @return void
     * @throws JsonException
     */

    public function testItReturnsErrorResponseIfNoTextProvided(): void
    {
        $request = new Request([],[],'{"author_uuid":"95267850-37ff-4277-9b80-511cc5d48905", "title":"title"}');

        $postRepository = $this->postsRepository([]);
        $userRepository = $this->usersRepository([
            new User(
                new UUID('95267850-37ff-4277-9b80-511cc5d48905'),
                new Name('Ivan', 'Nikitin'), 'Ivan',
            ),
        ]);

        $action = new CreatePost($userRepository, $postRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such field; text"}');

        $response->send();
    }

}