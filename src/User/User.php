<?php

namespace App\User;
//namespace App;

class User
{
    public function __construct(
        private int $id,
        private string $firstName,
        private string $lastName
    ) {
    }
}