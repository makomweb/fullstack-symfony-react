<?php

declare(strict_types=1);

namespace App\Instrumentation\Stdout;

use App\Game\Instrumentation\Tracer;
use App\Game\Instrumentation\TracingInterface;
use PHPMolecules\DDD\Attribute\Service;
use Psr\Log\LoggerInterface;

#[Service]
final readonly class Tracing implements TracingInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * @param non-empty-string     $methodName
     * @param non-empty-string     $file
     * @param array<string,string> $traceContext
     */
    public function createTracer(string $methodName, string $file, array $traceContext = []): Tracer
    {
        return new Tracer(new Trace($methodName, $this->logger));
    }
}
