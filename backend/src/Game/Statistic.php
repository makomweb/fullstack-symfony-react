<?php

declare(strict_types=1);

namespace App\Game;

use App\Invariant\Ensure;
use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final readonly class Statistic implements \Stringable
{
    private function __construct(
        public string $gameName,
        public string $gameId,
        public int $homePoints = 0,
        public int $guestPoints = 0,
    ) {
    }

    public static function initial(string $gameName, string $gameId): self
    {
        return new self($gameName, $gameId);
    }

    /** @param non-empty-string $team */
    public function withScore(string $team): self
    {
        Ensure::that('home' === $team || 'guest' === $team);

        return new self(
            $this->gameName,
            $this->gameId,
            'home' === $team ? $this->homePoints + 1 : $this->homePoints,
            'guest' === $team ? $this->guestPoints + 1 : $this->guestPoints,
        );
    }

    public function getPointsAsString(): string
    {
        return sprintf('%d : %d', $this->homePoints, $this->guestPoints);
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->gameName, $this->getPointsAsString());
    }
}
