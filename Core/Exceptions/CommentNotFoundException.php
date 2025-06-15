<?php

namespace App\Core\Exceptions;

use Exception;

class CommentNotFoundException extends Exception
{
    public function __construct($message = 'Comentário não encontrado.', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}