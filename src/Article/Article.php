<?php

namespace Lite\php2\Article;

class Article
{
    public function __construct(
        private int $id,
        private int $iaAuthor,
        private string $heading,
        private string $text
    ) {
    }
}