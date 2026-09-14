<?php

namespace App\Domains\VisualBoard\Exceptions;

use DomainException;

class UnauthorizedVerificationException extends DomainException
{
    public function __construct(string $message = 'Anda tidak memiliki akses untuk memverifikasi temuan ini.')
    {
        parent::__construct($message);
    }
}
