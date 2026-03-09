<?php

declare(strict_types=1);

namespace App\Game;

use App\Game\Exception\GameAlreadyExistsException;
use App\Game\Exception\GameDoesNotExistException;
use App\Game\Instrumentation\LoggingInterface;
use App\Timer\Stopwatch;
use PHPMolecules\DDD\Attribute\AggregateRoot;

#[AggregateRoot]
final readonly class Games
{
    public const UNIQUE_ID = '851f850b-f9b1-4a54-858b-7b5f9cbbcdb9';

    public function __construct(
        private CacheInterface $cache,
        private EventStoreInterface $eventStore,
        private GamesReducer $reducer,
        private LoggingInterface $logging,
    ) {
    }

    /**
     * Provide the index of games.
     * By default this is served from the cache if it is present there.
     * If it is not present the games are rebuild from the underlying event store and served.
     *
     * When cache refresh is enforced the index is rebuild,
     * stored into the cache for later, and served.
     *
     * @return Game[]
     */
    public function index(bool $forceCacheRefresh = false): array
    {
        return $this->cache->getValue(
            self::UNIQUE_ID,
            fn () => $this->createIndex(),
            $forceCacheRefresh
        );
    }

    public function find(string $gameId, bool $forceCacheRefresh = false): Game
    {
        $index = $this->index($forceCacheRefresh);

        foreach ($index as $game) {
            if ($game->getId() === $gameId) {
                return $game;
            }
        }

        throw new GameDoesNotExistException($gameId);
    }

    /**
     * Check if the specified game ID exists in the index.
     */
    public function exists(string $gameId, bool $forceCacheRefresh = false): bool
    {
        $index = $this->index($forceCacheRefresh);

        foreach ($index as $game) {
            if ($game->getId() === $gameId) {
                return true;
            }
        }

        return false;
    }

    /** Add a game. */
    public function add(Game $newGame): void
    {
        $index = $this->index(
            /* We trust that the cache is fresh. */
            forceCacheRefresh: false
        );

        foreach ($index as $game) {
            if ($game->getId() === $newGame->getId()) {
                throw new GameAlreadyExistsException($newGame->getId());
            }
        }

        $this->cache->store(self::UNIQUE_ID, [...$index, $newGame]);
    }

    /** Remove a game. */
    public function remove(string $gameId): void
    {
        $index = $this->index(
            /* We trust that the cache is fresh. */
            forceCacheRefresh: false
        );

        if (!array_find($index, static fn (Game $g) => $g->getId() === $gameId)) {
            throw new GameDoesNotExistException($gameId);
        }

        $rest = array_values(array_filter($index, static fn (Game $g) => $g->getId() !== $gameId));

        $this->cache->store(self::UNIQUE_ID, $rest);
    }

    /**
     * @return Game[]
     */
    private function createIndex(): array
    {
        $stopWatch = Stopwatch::start();

        try {
            // TODO: Fake the following 2 options to check robustness of the system!
            // throw new \Exception('💥 Boom!');
            // sleep(8);
            return array_reduce($this->getEvents(), $this->reducer, []);
        } finally {
            $this->logging->info(sprintf('⌚ Rebuilding index took: %.0f ms', $stopWatch->getMillisecondsElapsed()));
        }
    }

    /**
     * @return Event[]
     */
    private function getEvents(): array
    {
        $options = new QueryOptions(
            subjectTypes: ['Games'],
            eventTypes: ['added', 'removed']
        );

        return $this->eventStore->getEvents($options);
    }
}
