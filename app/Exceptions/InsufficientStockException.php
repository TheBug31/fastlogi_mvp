<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a stock reservation or adjustment would leave a product with a
 * negative stock value (RF-001, RF-008).
 */
class InsufficientStockException extends RuntimeException {}
