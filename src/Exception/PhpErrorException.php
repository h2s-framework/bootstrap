<?php

namespace Siarko\Bootstrap\Exception;

use Throwable;

class PhpErrorException extends \Exception
{

    public function __construct(
        string $message,
        int $code,
        ?string $file = '',
        ?int $line = 0,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
        $this->file = $file;
        $this->line = $line;
    }

}