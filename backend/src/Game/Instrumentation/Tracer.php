<?php

declare(strict_types=1);

namespace App\Game\Instrumentation;

use PHPMolecules\DDD\Attribute\Service;

/**
 * When an instance of this class goes out of scope
 * it calls trace::end() to indicate the trace is finished.
 */
#[Service]
final readonly class Tracer
{
    public function __construct(private TraceInterface $trace)
    {
        $trace->start();
    }

    public function recordException(\Throwable $ex): void
    {
        $this->trace->recordException($ex);
    }

    public function __destruct()
    {
        $this->trace->end();
    }
}
