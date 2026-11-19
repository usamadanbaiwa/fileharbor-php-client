<?php
declare(strict_types=1);

namespace FileHarbor\Client;

final class FileHarborException extends \RuntimeException
{
    public readonly string $errorCode;
    public readonly ?int $status;

    public function __construct(
        string $message,
        string $errorCode,
        ?int $status = null,
    ) {
        parent::__construct($message);

        $this->errorCode = $errorCode;
        $this->status = $status;
    }
}
