<?php

declare(strict_types=1);

namespace App\Message;

use App\Entity\Event;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'async')]
final readonly class EventPersisted
{
    /** @param array<string,string> $traceContext */
    private function __construct(
        public Event $event,
        public float $createdAt,
        public array $traceContext,
    ) {
    }

    public static function fromEvent(Event $event): self
    {
        return new self(
            $event,
            microtime(true),
            self::provideTraceContext()
        );
    }

    /** @return array<string,string> */
    private static function provideTraceContext(): array
    {
        $carrier = [];

        TraceContextPropagator::getInstance()->inject($carrier);
        assert(is_array($carrier));

        foreach ($carrier as $key => $value) {
            assert(is_string($key));
            assert(is_string($value));
        }

        return $carrier; // @phpstan-ignore return.type
    }
}
