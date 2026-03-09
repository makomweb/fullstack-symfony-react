<?php

declare(strict_types=1);

namespace App\Game\Command\Executor;

use App\Game\Command\AddGameCommand;
use App\Game\Command\IncrementScoreCommand;
use App\Game\Command\RemoveGameCommand;
use App\Game\CommandInterface;
use App\Game\Event;
use App\Game\Games;
use App\Game\NormalizerInterface;
use PHPMolecules\DDD\Attribute\Factory;

#[Factory]
final readonly class EventFactory
{
    public function __construct(private NormalizerInterface $normalizer)
    {
    }

    public function fromCommand(CommandInterface $command): Event
    {
        return match (get_class($command)) {
            AddGameCommand::class => new Event(
                subjectType: 'Games',
                subjectId: Games::UNIQUE_ID,
                eventType: 'added',
                payload: $this->normalizer->toArray($command, ignoreFields: ['gameId'])
            ),
            IncrementScoreCommand::class => new Event(
                subjectType: 'Game',
                subjectId: $command->gameId,
                eventType: 'scored',
                payload: $this->normalizer->toArray($command, ignoreFields: ['gameId'])
            ),
            RemoveGameCommand::class => new Event(
                subjectType: 'Games',
                subjectId: Games::UNIQUE_ID,
                eventType: 'removed',
                payload: $this->normalizer->toArray($command)
            ),
            default => throw new \InvalidArgumentException(sprintf('Command of type "%s" not supported!', get_debug_type($command))),
        };
    }
}
