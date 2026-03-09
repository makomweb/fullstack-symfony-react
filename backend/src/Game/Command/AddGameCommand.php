<?php

declare(strict_types=1);

namespace App\Game\Command;

use App\Game\GameId;
use App\Game\GameName;
use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final class AddGameCommand extends GameCommand
{
    public function __construct(
        public readonly \DateTimeImmutable $dateTime,
        public readonly string $home,
        public readonly string $guest,
    ) {
        parent::__construct(GameId::create(GameName::create($dateTime, $home, $guest)));
    }
}
