<?php

namespace App\Blog\Repositories\CommentsRepository;

use App\Blog\Comment;
use App\Blog\Post;
use App\Blog\UUID;

interface CommentsRepositoryInterface
{
    public function save(Comment $comment): void;
    public function get(UUID $uuid): Comment;

    //public function delete(UUID $uuid): void;
}