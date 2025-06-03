<?php

namespace App\Core\Middleware;

use App\Core\Spec\ProceduralMiddleware;

class AuthGuard implements ProceduralMiddleware
{

    private string $currentUri;

    /**
     * @var array Whitelist de páginas públicas
     */
    private array $excludedUris = [
        'login.php',
        'registo.php',
        'logout.php',
        'backsoon.php'
    ];

    public function __construct()
    {
        $this->currentUri = $_SERVER['REQUEST_URI'];
    }

    /**
     * @return void
     */
   public function run(): void
   {
       $isExcluded = false;
       foreach ($this->excludedUris as $uri) {
           if (str_contains($this->currentUri, $uri)) {
               $isExcluded = true;
               break;
           }
       }
       if (!$isExcluded && !is_logged_in()) {
           response('/login.php');
       }
   }

    /**
     * @param callable $callableMiddleware
     * @return mixed
     */
    public function next(callable $callableMiddleware)
    {
        return call_user_func($callableMiddleware);
    }
}