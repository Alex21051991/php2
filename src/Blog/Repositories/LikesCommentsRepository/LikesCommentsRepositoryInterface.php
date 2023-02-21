<?php

namespace App\Blog\Repositories\LikesCommentsRepository;

use App\Blog\Like;
use App\Blog\UUID;

interface LikesCommentsRepositoryInterface
{
    public function save(Like $like) : void;
    public function getByCommentUuid(UUID $uuid) : array;
}