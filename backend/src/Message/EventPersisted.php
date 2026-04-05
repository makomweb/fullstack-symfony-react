<?php

declare(strict_types=1);

namespace App\Message;

use App\Entity\Event;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'async')]
final class EventPersisted
{
    /** @var array<string,string> */
    private array $traceContext;

    private function __construct(
        public readonly Event $event,
        public readonly float $createdAt,
    ) {
        $this->traceContext = [];
    }

    public static function fromEvent(Event $event): self
    {
        $obj = new self($event, microtime(true));

        self::addTraceContext($obj);

        return $obj;
    }

    /** @return array<string,string> */
    public function getTraceContext(): array
    {
        return $this->traceContext;
    }

    private static function addTraceContext(self $message): void
    {
        $traceContext = [];

        TraceContextPropagator::getInstance()->inject($traceContext);
        assert(is_array($traceContext));

        foreach ($traceContext as $key => $value) {
            assert(is_string($key));
            assert(is_string($value));
            $message->traceContext[$key] = $value;
        }
    }
}
