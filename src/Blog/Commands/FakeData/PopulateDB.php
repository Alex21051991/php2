<?php

namespace App\Blog\Commands\FakeData;

use App\Blog\Comment;
use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Post;
use App\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\User;
use App\Blog\UUID;
use App\Person\Name;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateDB extends Command
{
    // Внедряем генератор тестовых данных и репозитории пользователей и статей
    public function __construct(
        private \Faker\Generator $faker,
        private UsersRepositoryInterface $usersRepository,
        private PostsRepositoryInterface $postsRepository,
        private CommentsRepositoryInterface $commentsRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('fake-data:populate-db')
            ->setDescription('Populates DB with fake data')
            ->addOption('users-number','u', InputOption::VALUE_OPTIONAL, 'количество создаваемых пользователей')
            ->addOption('posts-number','p', InputOption::VALUE_OPTIONAL, 'количество создаваемых постов');
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $usersNumber = $input->getOption('users-number');
        $postsNumber = $input->getOption('posts-number');

        // Установим значение по умолчанию, если опции пусты
        if (empty($usersNumber)) {
            $usersNumber = 2;
        }

        if (empty($postsNumber)) {
            $postsNumber = 3;
        }

        // Создаём пользователей
        $users = [];
        for ($i = 0; $i < $usersNumber; $i++) {
            $user = $this->createFakeUser();
            $users[] = $user;
            //$output->writeln('User created: ' . $user->username());
        }

        // От имени каждого пользователя создаём статьи
        $posts = [];
        foreach ($users as $user) {
            for ($i = 0; $i < $postsNumber; $i++) {
                $post = $this->createFakePost($user);
                $posts[] = $post;
                //$output->writeln('Post created: ' . $post->getTitle());
            }
        }

        // Для каждой статью создаем по 2 комментария
        foreach ($posts as $post) {
            for ($i = 0; $i < 2; $i++) {
                $comment = $this->createFakeComment($post, $user);
                //$output->writeln('Comment created: ' . $comment->getText());
            }
        }
        return Command::SUCCESS;
    }

    /**
     * @param Post $post
     * @param User $user
     * @return Comment
     * @throws InvalidArgumentException
     */
    private function createFakeComment(Post $post,User $user): Comment
    {
        $comment = new Comment(
            UUID::random(),
            $post,
            $user,
            $this->faker->realText,
        );

        // Сохраняем комментарий
        $this->commentsRepository->save($comment);
        return $comment;
    }

    private function createFakeUser(): User
    {
        $user = User::createFrom(
            // Генерируем имя пользователя
            $this->faker->userName,
            // Генерируем пароль
            $this->faker->password,
            new Name(
                // Генерируем имя
                $this->faker->firstName,
                // Генерируем фамилию
                $this->faker->lastName
            )
        );
        // Сохраняем пользователя в репозиторий
        $this->usersRepository->save($user);
        return $user;
    }

    /**
     * @param User $user
     * @return Post
     * @throws InvalidArgumentException
     */
    private function createFakePost(User $user): Post
    {
        $post = new Post(
            UUID::random(),
            $user,
            $this->faker->sentence(6, true),
            $this->faker->realText,
        );

        // Сохраняем статью в репозиторий
        $this->postsRepository->save($post);
        return $post;
    }
}
