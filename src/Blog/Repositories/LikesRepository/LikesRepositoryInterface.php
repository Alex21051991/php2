<?php

namespace App\Blog\Repositories\LikesRepository;

use App\Blog\Like;
use App\Blog\UUID;

interface LikesRepositoryInterface
{
    public function save(Like $like) : void;
    public function getByPostUuid(UUID $uuid) : array;
}