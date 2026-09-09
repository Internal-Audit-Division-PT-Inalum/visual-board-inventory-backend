<?php

namespace App\Domains\Inventory\Exceptions;

use RuntimeException;

/**
 * Dilempar oleh InventoryService ketika tipe operasi tidak sesuai
 * dengan tipe barang (misal: take pada asset, borrow pada consumable).
 *
 * Ditangkap oleh Global Exception Handler di bootstrap/app.php → HTTP 422.
 */
class InvalidItemOperationException extends RuntimeException {}
