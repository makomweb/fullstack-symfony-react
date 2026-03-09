<?php

declare(strict_types=1);

namespace App\Game\Command;

use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final class IncrementScoreCommand extends GameCommand
{
    /**
     * @param non-empty-string $gameId
     * @param non-empty-string $team
     */
    public function __construct(
        string $gameId,
        public readonly string $team,
        public readonly int $playerId,
        public readonly \DateTimeImmutable $timestamp = new \DateTimeImmutable(),
    ) {
        parent::__construct($gameId);
    }
}
