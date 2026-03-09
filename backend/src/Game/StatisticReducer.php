<?php

declare(strict_types=1);

namespace App\Game;

use App\Invariant\Ensure;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
final readonly class StatisticReducer
{
    /**
     * Reduce the given statistic object with the specified event.
     */
    public function __invoke(Statistic $statistics, Event $event): Statistic
    {
        Ensure::that($statistics->gameId === $event->subjectId);
        Ensure::that('scored' === $event->eventType);

        $team = $event->getValue('team');
        assert(is_string($team) && strlen($team) > 0);

        return $statistics->withScore($team);
    }
}
