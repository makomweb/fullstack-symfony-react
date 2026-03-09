<?php

declare(strict_types=1);

namespace App\Instrumentation\Noop;

use App\Game\Instrumentation\TraceInterface;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
final readonly class Trace implements TraceInterface
{
    public function start(): void
    {
    }

    public function recordException(\Throwable $ex): void
    {
    }

    public function end(): void
    {
    }
}
