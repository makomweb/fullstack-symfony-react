<?php

declare(strict_types=1);

namespace App\Instrumentation\Otel;

use App\Game\Instrumentation\Tracer;
use App\Game\Instrumentation\TracingInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\TracerProviderInterface as OtelTracerProviderInterface;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use PHPMolecules\DDD\Attribute\Service;
use Psr\Log\LoggerInterface;

#[Service]
final readonly class Tracing implements TracingInterface
{
    private OtelTracerProviderInterface $provider;

    public function __construct(private LoggerInterface $logger)
    {
        $this->provider = Globals::tracerProvider();
    }

    /**
     * @param non-empty-string     $methodName
     * @param non-empty-string     $file
     * @param array<string,string> $traceContext
     */
    public function createTracer(string $methodName, string $file, array $traceContext = []): Tracer
    {
        assert($this->provider instanceof TracerProviderInterface);

        return new Tracer(
            new Trace([
                new TraceLog($methodName, $this->logger),
                new TraceSpan($this->provider, $methodName, $file, $traceContext),
            ])
        );
    }
}
