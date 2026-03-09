<?php

declare(strict_types=1);

namespace App\Game\Exception;

use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final class GameDoesNotExistException extends GameException
{
    public function __construct(string $gameId)
    {
        parent::__construct(
            sprintf('🚩 There is no game with ID "%s"!', $gameId),
            404 // Not Found
        );
    }
}
