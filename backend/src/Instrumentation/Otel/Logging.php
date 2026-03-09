<?php

declare(strict_types=1);

namespace App\Instrumentation\Otel;

use App\Game\Instrumentation\LoggingInterface;
use PHPMolecules\DDD\Attribute\Service;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

#[Service]
final readonly class Logging implements LoggingInterface
{
    public function __construct(
        private PsrLoggerInterface $logger,
    ) {
    }

    public function info(string|\Stringable $message): void
    {
        $this->logger->info($message);
    }

    public function exception(\Throwable $ex): void
    {
        $this->logger
            ->error($ex->getMessage(), [
                'exception_type' => get_class($ex),
                'stack_trace' => $ex->getTrace(),
            ]);
    }
}
