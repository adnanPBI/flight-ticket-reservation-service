<?php

namespace App\Exceptions\Flight;

use RuntimeException;
use Throwable;

class ProviderException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly bool $retryable = false,
        public readonly ?int $statusCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function transient(string $message, ?int $statusCode = null, ?Throwable $previous = null): self
    {
        return new self($message, true, $statusCode, $previous);
    }

    public static function permanent(string $message, ?int $statusCode = null, ?Throwable $previous = null): self
    {
        return new self($message, false, $statusCode, $previous);
    }

    public function isRetryable(): bool
    {
        return $this->retryable;
    }
}
