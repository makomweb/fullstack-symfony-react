<?php

declare(strict_types=1);

namespace App\Game;

use App\Game\Instrumentation\LoggingInterface;
use App\Timer\Stopwatch;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
class Statistics
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly EventStoreInterface $eventStore,
        private readonly StatisticReducer $reducer,
        private readonly LoggingInterface $logging,
    ) {
    }

    public function getStatistic(Game $game, bool $forceCacheRefresh = false): Statistic
    {
        $gameId = $game->getId();

        $statistic = $this->cache->getValue(
            $gameId,
            fn () => $this->createStatistic($game->getName(), $gameId),
            $forceCacheRefresh
        );

        assert($statistic instanceof Statistic);

        return $statistic;
    }

    public function remove(string $gameId): void
    {
        $this->cache->remove($gameId);
    }

    private function createStatistic(string $gameName, string $gameId): Statistic
    {
        $stopWatch = Stopwatch::start();

        try {
            // TODO: Fake the following 2 options to check robustness of the system!
            // throw new \Exception('💥 Boom!'); // [1]
            // sleep(8); // [2]
            return array_reduce(
                $this->getEvents($gameId),
                $this->reducer,
                Statistic::initial($gameName, $gameId)
            );
        } finally {
            $this->logging->info(
                sprintf(
                    '⌚ Rebuilding statistics: %s - %.0f ms',
                    $gameName, $stopWatch->getMillisecondsElapsed()
                )
            );
        }
    }

    /**
     * @return Event[]
     */
    private function getEvents(string $gameId): array
    {
        $options = new QueryOptions(
            subjectTypes: ['Game'],
            subjectIds: [$gameId],
            eventTypes: ['scored'],
        );

        return $this->eventStore->getEvents($options);
    }
}
