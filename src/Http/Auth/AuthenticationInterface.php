<?php

namespace App\Http\Auth;

use App\Blog\User;
use App\Http\Request;

interface AuthenticationInterface
{
    public function user(Request $request): User;
}