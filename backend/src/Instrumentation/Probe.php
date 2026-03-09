<?php

declare(strict_types=1);

namespace App\Instrumentation;

use App\Game\Command\Executor\ProbeInterface;
use App\Game\Instrumentation\LoggingInterface;
use App\Game\Instrumentation\MetricsInterface;
use App\Timer\Stopwatch;
use Symfony\Component\Stopwatch\Stopwatch as SymfonyStopwatch;

final class Probe implements ProbeInterface
{
    private Stopwatch $stopwatch;

    public function __construct(
        private readonly SymfonyStopwatch $symfonyStopwatch,
        private readonly LoggingInterface $logging,
        private readonly MetricsInterface $metrics,
        private readonly string $id,
        private readonly string $description,
    ) {
        $this->symfonyStopwatch->start($this->id, 'probe');
        $this->stopwatch = Stopwatch::start();
    }

    public function __destruct()
    {
        $this->symfonyStopwatch->stop($this->id);
        $millisecondsElapsed = $this->stopwatch->getMillisecondsElapsed();
        $this->logging->info(
            sprintf('%s took %.0f ms', $this->description, $millisecondsElapsed)
        );
        $this->metrics->record($this->id, $millisecondsElapsed, 'ms');
    }
}
