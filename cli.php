<?php

use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use App\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use App\Blog\UUID;
//use App\Blog\User;
//use App\Blog\Post;
//use App\Blog\Comment;
//use App\Person\Name;

require_once __DIR__ . '/vendor/autoload.php';

$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

$usersRepository = new SqliteUsersRepository($connection);
$postsRepository = new SqlitePostsRepository($connection);
$commentsRepository = new SqliteCommentsRepository($connection);

try {
    //$user = $usersRepository->save(new User(UUID::random(), new Name('Fedor2', 'Konuchov'), 'user4'));
    $user = $usersRepository->get(new UUID('26f8f204-baa9-434b-8114-0e89254baf8d'));

    //$post = $postsRepository->save(new Post(UUID::random(), $user, 'Заголовок', 'Текст поста'));
    $post = $postsRepository->get(new UUID('a7bfa0e1-e276-408f-b086-4a6d5d3d8fe7'));

    //$comment = $commentsRepository->save(new Comment(UUID::random(), $post, $user, 'Текст коммента к посту Анны Петровой'));
    $comment = $commentsRepository->get(new UUID('67b79646-63c6-42ed-949a-c218afcbd85c'));

    print_r($comment);

    //var_dump($user);
    //   /* $post = new Post(UUID::random(), $user, 'Заголовок', 'Текст поста' );*/

} catch (Exception $e) {
    echo $e->getMessage();
}

/*
//$usersRepository->save(new User(UUID::random(), new Name('Anna', 'Petrova'), 'login'));
$command = new CreateUserCommand($usersRepository);

try {
    //$usersRepository->save(new User(UUID::random(), new Name('Ivan', 'Nikitin'), "admin"));
    //echo $usersRepository->getByUsername('admin');
    $command->handle(Arguments::fromArgv($argv));
} catch (Exception $e) {
    echo $e->getMessage();
}
*/

/*
$faker = Faker\Factory::create('ru_RU');
//echo $faker->name() . PHP_EOL;
//echo $faker->realText(rand(100,200)) . PHP_EOL;
$name = new Name(
    $faker->firstName('male'),
    $faker->lastName()
);

$user = new User(
    $faker->randomDigitNotNull(),
    $name,
    $faker->sentence(1)
);
*/