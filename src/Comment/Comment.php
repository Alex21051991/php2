<?php

namespace App\Comment;

class Comment
{
    public function __construct(
        private int $id,
        private int $iaAuthor,
        private int $idArticle,
        private string $text
    ) {
    }
}