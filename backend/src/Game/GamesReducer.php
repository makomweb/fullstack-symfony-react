<?php

declare(strict_types=1);

namespace App\Game;

use App\Invariant\Ensure;
use App\Invariant\InvariantException;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
final readonly class GamesReducer
{
    public function __construct(private DenormalizerInterface $denormalizer)
    {
    }

    /**
     * Reduces the given collection of games with the specified event.
     *
     * @param Game[] $games the original collection of games might be empty when starting to reduce
     * @param Event  $event The event, e.g. from the event store.
     *
     * @return Game[] the resulting collection
     */
    public function __invoke(array $games, Event $event): array
    {
        Ensure::that('Games' === $event->subjectType);

        switch ($event->eventType) {
            case 'added':
                $game = $this->denormalizer->fromArray($event->payload, Game::class);
                assert($game instanceof Game);
                $games[] = $game;

                break;

            case 'removed':
                $gameId = $event->payload['game_id'];
                $games = array_values(array_filter($games, static fn (Game $game) => $game->getId() !== $gameId));

                break;

            default:
                throw new InvariantException(sprintf('Event type "%s" is not supported!', $event->eventType));
        }

        return $games;
    }
}
