<?php

namespace App\Domains\Inventory\Exceptions;

use Exception;

class IdempotencyConflictException extends Exception
{
    public function __construct(string $message = 'Idempotency conflict: The client_uuid has been used by another user.', int $code = 409, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
