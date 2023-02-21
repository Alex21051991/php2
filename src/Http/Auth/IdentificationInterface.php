<?php

namespace App\Http\Auth;

use App\Blog\User;
use App\Http\Request;

interface IdentificationInterface
{
    public function user(Request $request): User;
}