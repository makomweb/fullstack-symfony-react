<?php

declare(strict_types=1);

namespace App\Listener;

use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Context;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final readonly class TraceContextGetter implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $traceLogger)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $headers = $request->headers;

        $carrier = [];
        foreach ($headers as $key => $value) {
            $carrier[strtolower($key)] = $value;
        }

        Context::storage()->attach(
            TraceContextPropagator::getInstance()->extract($carrier)
        );

        $this->traceLogger->debug('Carrier extracted', ['carrier' => $carrier]);
    }

    /** @return array<string,string> */
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }
}
