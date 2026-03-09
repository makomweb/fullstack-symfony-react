<?php

declare(strict_types=1);

namespace App\Listener;

use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

#[AsEventListener]
final readonly class TraceContextSetter
{
    public function __construct(private LoggerInterface $traceLogger)
    {
    }

    public function __invoke(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        $carrier = [];

        TraceContextPropagator::getInstance()->inject($carrier);
        assert(is_array($carrier));

        $this->traceLogger->debug('Carrier injected', ['carrier' => $carrier]);

        foreach ($carrier as $key => $value) {
            assert(is_string($value));
            $response->headers->set($key, $value);
        }
    }
}
