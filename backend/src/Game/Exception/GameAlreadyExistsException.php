<?php

declare(strict_types=1);

namespace App\Game\Exception;

use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final class GameAlreadyExistsException extends GameException
{
    public function __construct(string $gameId)
    {
        parent::__construct(
            sprintf('🚩 There is already a game with ID "%s"!', $gameId),
            400 // Bad Request
        );
    }
}
