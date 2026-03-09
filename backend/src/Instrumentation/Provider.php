<?php

declare(strict_types=1);

namespace App\Instrumentation;

use App\Game\Instrumentation\LoggingInterface;
use App\Game\Instrumentation\MetricsInterface;
use App\Game\Instrumentation\TracingInterface;
use App\Instrumentation\Noop\Logging as NoopLogging;
use App\Instrumentation\Noop\Metrics as NoopMetrics;
use App\Instrumentation\Noop\Tracing as NoopTracing;
use App\Instrumentation\Otel\Logging as OtelLogging;
use App\Instrumentation\Otel\Metrics as OtelMetrics;
use App\Instrumentation\Otel\Tracing as OtelTracing;
use App\Instrumentation\Stdout\Logging as StdoutLogging;
use App\Instrumentation\Stdout\Metrics as StdoutMetrics;
use App\Instrumentation\Stdout\Tracing as StdoutTracing;
use PHPMolecules\DDD\Attribute\Service;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Service]
final readonly class Provider
{
    public function __construct(
        #[Autowire('%app.telemetry%')]
        private string $telemetry,
        private LoggerInterface $logger,
    ) {
    }

    public function provideLogging(): LoggingInterface
    {
        return match ($this->telemetry) {
            'Stdout' => new StdoutLogging($this->logger),
            'Noop' => new NoopLogging(),
            'Otel' => new OtelLogging($this->logger),
            default => throw new \InvalidArgumentException("{$this->telemetry} not supported!"),
        };
    }

    public function provideMetrics(): MetricsInterface
    {
        return match ($this->telemetry) {
            'Stdout' => new StdoutMetrics($this->logger),
            'Noop' => new NoopMetrics(),
            'Otel' => new OtelMetrics(),
            default => throw new \InvalidArgumentException("{$this->telemetry} not supported!"),
        };
    }

    public function provideTracing(): TracingInterface
    {
        return match ($this->telemetry) {
            'Stdout' => new StdoutTracing($this->logger),
            'Noop' => new NoopTracing(),
            'Otel' => new OtelTracing($this->logger),
            default => throw new \InvalidArgumentException("{$this->telemetry} not supported!"),
        };
    }
}
