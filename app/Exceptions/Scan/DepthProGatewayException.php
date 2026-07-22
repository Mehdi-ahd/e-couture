<?php

namespace App\Exceptions\Scan;

use RuntimeException;
use Throwable;

/**
 * Exception levee lors d un echec de communication avec l API Depth Pro.
 */
class DepthProGatewayException extends RuntimeException
{
    public function __construct(
        string $message = 'Depth Pro service request failed.',
        public readonly ?int $statusCode = null,
        public readonly array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
