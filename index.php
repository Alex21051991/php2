<?php
//"Lite\\php2\\": "src/"
require_once __DIR__ . '/vendor/autoload.php';

//use Lite\php2\User\User;
use App\Blog\User;

$user =0;
$user->save(new User(UUID::random(), new Name('Ivan', 'Nikitin'), 'login'));

var_dump($user);