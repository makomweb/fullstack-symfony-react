<?php

declare(strict_types=1);

namespace App\Instrumentation\Otel;

use App\Game\Instrumentation\TraceInterface;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
final readonly class Trace implements TraceInterface
{
    /** @param TraceInterface[] $traces */
    public function __construct(private array $traces)
    {
    }

    public function start(): void
    {
        foreach ($this->traces as $trace) {
            $trace->start();
        }
    }

    public function recordException(\Throwable $ex): void
    {
        foreach ($this->traces as $trace) {
            $trace->recordException($ex);
        }
    }

    public function end(): void
    {
        foreach ($this->traces as $trace) {
            $trace->end();
        }
    }
}
