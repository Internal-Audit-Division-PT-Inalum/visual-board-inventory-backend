<?php

namespace App\Domains\Inventory\Exceptions;

use Exception;

class LoanExceedsOutstandingException extends Exception
{
    public function __construct(string $message = 'Jumlah pengembalian melebihi sisa pinjaman.')
    {
        parent::__construct($message, 422);
    }
}
