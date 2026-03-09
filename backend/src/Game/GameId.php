<?php

declare(strict_types=1);

namespace App\Game;

use PHPMolecules\DDD\Attribute\Factory;

#[Factory]
final readonly class GameId
{
    /** @return non-empty-string */
    public static function create(string $name): string
    {
        $salt = 'bb322089-32f2-412c-956b-36c5e3a57eb4';

        return hash('sha256', $name.$salt);
    }
}
