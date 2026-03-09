<?php

declare(strict_types=1);

namespace App\Game\Instrumentation;

interface TracingInterface
{
    /**
     * When an instance of the Tracer class goes out of scope
     * it calls trace::end() to indicate the trace is finished.
     *
     * @param non-empty-string     $methodName
     * @param non-empty-string     $file
     * @param array<string,string> $tracingContext
     */
    public function createTracer(string $methodName, string $file, array $tracingContext = []): Tracer;
}
