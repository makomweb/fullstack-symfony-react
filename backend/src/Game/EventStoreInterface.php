<?php

declare(strict_types=1);

namespace App\Game;

interface EventStoreInterface
{
    /** @return Event[] */
    public function getEvents(QueryOptions $options = new QueryOptions()): array;

    public function persist(Event $event, bool $dontFlush = false): void;

    public function reset(): void;
}
