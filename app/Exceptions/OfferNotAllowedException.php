<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an order cannot be offered to a driver: the driver belongs to
 * another business, is offline or has reached their maximum capacity
 * (RF-002, RF-005).
 */
class OfferNotAllowedException extends RuntimeException {}
