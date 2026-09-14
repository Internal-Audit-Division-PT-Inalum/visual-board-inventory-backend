<?php

namespace App\Domains\VisualBoard\Exceptions;

use RuntimeException;

class ScheduleNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Jadwal bulanan tidak ditemukan.')
    {
        parent::__construct($message);
    }
}
