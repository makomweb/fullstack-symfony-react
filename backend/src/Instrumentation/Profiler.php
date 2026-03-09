<?php

declare(strict_types=1);

namespace App\Instrumentation;

use App\Game\Command\Executor\ProbeInterface;
use App\Game\Command\Executor\ProfilerInterface;
use App\Game\Instrumentation\LoggingInterface;
use App\Game\Instrumentation\MetricsInterface;
use Symfony\Component\Stopwatch\Stopwatch as ProfilerStopwatch;

final class Profiler implements ProfilerInterface
{
    public function __construct(
        private readonly ProfilerStopwatch $profilerStopwatch,
        private readonly LoggingInterface $logging,
        private readonly MetricsInterface $metrics,
    ) {
    }

    public function plantProbe(string $id, string $description): ProbeInterface
    {
        return new Probe(
            $this->profilerStopwatch,
            $this->logging,
            $this->metrics,
            $id,
            $description
        );
    }
}
