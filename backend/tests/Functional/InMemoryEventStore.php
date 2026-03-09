<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Game\Event;
use App\Game\EventStoreInterface;
use App\Game\QueryOptions;

final class InMemoryEventStore implements EventStoreInterface
{
    /** @param Event[] $events */
    public function __construct(private array $events = [])
    {
    }

    /** @return Event[] */
    public function getEvents(QueryOptions $options = new QueryOptions()): array
    {
        if ($options->isEmpty()) {
            return $this->events;
        }

        return array_filter(
            $this->events,
            static fn (Event $event) => in_array($event->subjectType, $options->subjectTypes)
        );
    }

    public function persist(Event $event, bool $dontFlush = false): void
    {
        $this->events[] = $event;
    }

    public function reset(): void
    {
        $this->events = [];
    }
}
