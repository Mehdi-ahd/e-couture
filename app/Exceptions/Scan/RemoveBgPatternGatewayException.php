<?php

namespace App\Exceptions\Scan;

use RuntimeException;
use Throwable;

class RemoveBgPatternGatewayException extends RuntimeException
{
    public function __construct(
        string $message = 'Remove.bg pattern cutout request failed.',
        public readonly ?int $statusCode = null,
        public readonly array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
