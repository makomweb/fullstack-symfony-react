<?php

declare(strict_types=1);

namespace App\Game\Exception;

use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
abstract class GameException extends \Exception
{
    protected function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
