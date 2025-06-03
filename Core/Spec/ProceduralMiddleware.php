<?php

namespace App\Core\Spec;

interface ProceduralMiddleware
{
    public function run();

    public function next(Callable $callableMiddleware);
}