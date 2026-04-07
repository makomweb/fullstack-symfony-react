<?php

declare(strict_types=1);

namespace App\Message;

use App\Game\Instrumentation\LoggingInterface;
use App\Game\Instrumentation\MetricsInterface;
use App\Game\Instrumentation\TracingInterface;
use App\Timer\Stopwatch;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EventPersistedHandler
{
    public function __construct(
        private TracingInterface $tracing,
        private LoggingInterface $logging,
        private MetricsInterface $metrics,
    ) {
    }

    public function __invoke(EventPersisted $message): void
    {
        $this->tryHandle($message);
    }

    private function tryHandle(EventPersisted $message): void
    {
        $tracer = $this->tracing
            ->createTracer(__METHOD__, __FILE__, $message->traceContext);

        try {
            $this->handle($message);
        } catch (\Throwable $ex) {
            $tracer->recordException($ex);
            $this->logging->exception($ex);
            throw new HandlingFailedException($ex);
        } finally {
            $elapsed = Stopwatch::from($message->createdAt)->getMillisecondsElapsed();
            $this->logging->info(sprintf('⌚ Handling message took: %.0f ms', $elapsed));
            $this->metrics->record('message_handled', $elapsed, 'ms');
        }
    }

    private function handle(EventPersisted $message): void
    {
        $event = $message->event;

        switch ($event->getSubjectType()) {
            case 'Game':
            case 'Games':
                /* Do nothing at the moment. */
            default:
                break;
        }
    }
}
