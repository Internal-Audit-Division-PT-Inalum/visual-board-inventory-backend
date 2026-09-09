<?php

namespace App\Domains\Inventory\Exceptions;

use RuntimeException;

/**
 * Dilempar oleh InventoryService ketika stok barang tidak mencukupi
 * untuk operasi pengambilan (take/borrow).
 *
 * Ditangkap oleh Global Exception Handler di bootstrap/app.php → HTTP 422.
 */
class InsufficientStockException extends RuntimeException {}
