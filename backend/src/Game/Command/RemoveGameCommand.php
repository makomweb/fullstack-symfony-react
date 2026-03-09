<?php

declare(strict_types=1);

namespace App\Game\Command;

use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final class RemoveGameCommand extends GameCommand
{
    /** @param non-empty-string $gameId */
    public function __construct(
        string $gameId,
        public readonly \DateTimeImmutable $timestamp = new \DateTimeImmutable(),
    ) {
        parent::__construct($gameId);
    }
}
