<?php

declare(strict_types=1);

namespace App\Game\Instrumentation;

interface TraceInterface
{
    public function start(): void;

    public function recordException(\Throwable $ex): void;

    public function end(): void;
}
