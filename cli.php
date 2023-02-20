<?php

use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\UUID;
use App\Blog\Repositories\PostsRepository\SqlitePostsRepository;

require_once __DIR__ . '/vendor/autoload.php';

$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

$usersRepository = new SqliteUsersRepository($connection);
$postsRepository = new SqlitePostsRepository($connection);

try {
    $user = $usersRepository->get(new UUID('a7bfa0e1-e276-408f-b086-4a6d5d3d8fe6'));

    $post = $postsRepository->get(new UUID('a7bfa0e1-e276-408f-b086-4a6d5d3d8fe7'));
    print_r($post);
    //var_dump($user);
   /* $post = new Post(
        UUID::random(),
        $user,
        'Заголовок',
        'Текст поста'
    );*/


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

