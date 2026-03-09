<?php

declare(strict_types=1);

namespace App\Game\Instrumentation;

interface MetricsInterface
{
    public function recordMessage(string $name, string $message): void;

    public function record(string $name, float|int $value, string $unit): void;
}
