<?php

declare(strict_types=1);

namespace App\Instrumentation\Noop;

use App\Game\Instrumentation\Tracer;
use App\Game\Instrumentation\TracingInterface;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
final readonly class Tracing implements TracingInterface
{
    /**
     * @param non-empty-string     $methodName
     * @param non-empty-string     $file
     * @param array<string,string> $traceContext
     */
    public function createTracer(string $methodName, string $file, array $traceContext = []): Tracer
    {
        return new Tracer(new Trace());
    }
}
