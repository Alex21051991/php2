<?php

namespace App\Http\Actions;

use App\http\Request;
use App\http\Response;

interface ActionInterface
{
    public function handle(Request $request): Response;
}