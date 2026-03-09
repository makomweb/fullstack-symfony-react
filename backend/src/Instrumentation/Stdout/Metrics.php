<?php

declare(strict_types=1);

namespace App\Instrumentation\Stdout;

use App\Game\Instrumentation\MetricsInterface;
use PHPMolecules\DDD\Attribute\Service;
use Psr\Log\LoggerInterface;

#[Service]
final readonly class Metrics implements MetricsInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function recordMessage(string $name, string $message): void
    {
        $this->logger->notice("\"{$name}\" recorded {$message}");
    }

    public function record(string $name, float|int $value, string $unit): void
    {
        $this->logger->notice("\"{$name}\" recorded {$value} {$unit}");
    }

    public function flush(): void
    {
    }
}
