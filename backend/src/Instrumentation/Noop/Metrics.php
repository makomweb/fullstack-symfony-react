<?php

declare(strict_types=1);

namespace App\Instrumentation\Noop;

use App\Game\Instrumentation\MetricsInterface;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
final readonly class Metrics implements MetricsInterface
{
    public function recordMessage(string $name, string $message): void
    {
    }

    public function record(string $name, float|int $value, string $unit): void
    {
    }

    public function flush(): void
    {
    }
}
