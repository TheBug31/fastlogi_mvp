<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an operation is attempted on a suspended business (RF-003 §4.3.1).
 */
class BusinessSuspendedException extends RuntimeException {}
