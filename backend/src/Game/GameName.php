<?php

declare(strict_types=1);

namespace App\Game;

use PHPMolecules\DDD\Attribute\Factory;

#[Factory]
final readonly class GameName
{
    public static function create(\DateTimeImmutable $dateTime, string $home, string $guest): string
    {
        return sprintf(
            '%s - %s vs %s',
            $dateTime->format('Y-m-d H:i:s'),
            $home,
            $guest
        );
    }
}
