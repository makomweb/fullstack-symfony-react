<?php

declare(strict_types=1);

namespace App\Game\Command;

use App\Game\CommandInterface;
use App\Invariant\Ensure;
use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
abstract class GameCommand implements CommandInterface
{
    public function __construct(public readonly string $gameId)
    {
        Ensure::that(strlen($gameId) > 0);
    }
}
