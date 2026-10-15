<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a product cannot be deleted because it already belongs to orders
 * (removing it would corrupt the order history through the cascade delete).
 */
class ProductInUseException extends RuntimeException {}
