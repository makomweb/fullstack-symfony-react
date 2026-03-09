<?php

declare(strict_types=1);

namespace App\Game\Command;

use App\Game\CommandInterface;
use PHPMolecules\DDD\Attribute\Factory;

#[Factory]
final readonly class CommandDescription
{
    public function successMessageFor(CommandInterface $command): string
    {
        return match (get_class($command)) {
            AddGameCommand::class => '⚽ Game added',
            IncrementScoreCommand::class => sprintf('🙌 %s has scored! (player: %d)', ucfirst($command->team), $command->playerId),
            RemoveGameCommand::class => '🗑️ Game removed',
            default => throw new \InvalidArgumentException(sprintf('Class %s not supported!', get_debug_type($command))),
        };
    }

    public function for(CommandInterface $command): string
    {
        return match (get_class($command)) {
            AddGameCommand::class => 'Adding game',
            IncrementScoreCommand::class => 'Incrementing score',
            RemoveGameCommand::class => 'Removing game',
            default => throw new \InvalidArgumentException(sprintf('Class %s not supported!', get_debug_type($command))),
        };
    }
}
