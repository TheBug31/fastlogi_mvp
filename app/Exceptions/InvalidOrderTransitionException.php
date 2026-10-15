<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an order status change is not allowed by the transition matrix of
 * SRS §4.1.3 (RF-001).
 */
class InvalidOrderTransitionException extends RuntimeException {}
