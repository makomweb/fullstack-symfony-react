<?php

declare(strict_types=1);

namespace App\Instrumentation\Stdout;

use App\Game\Instrumentation\TraceInterface;
use PHPMolecules\DDD\Attribute\Service;
use Psr\Log\LoggerInterface;

#[Service]
final class Trace implements TraceInterface
{
    private ?\Throwable $recordedException;

    public function __construct(
        private string $methodName,
        private readonly LoggerInterface $logger,
    ) {
        $this->recordedException = null;
    }

    public function start(): void
    {
        $this->logger->debug('Entering '.$this->methodName);
    }

    public function recordException(\Throwable $ex): void
    {
        $this->recordedException = $ex;
    }

    public function end(): void
    {
        if ($this->recordedException) {
            $this->logger->debug(
                $this->recordedException->getMessage(),
                [
                    'exception_type' => get_class($this->recordedException),
                    'stack_trace' => $this->recordedException->getTrace(),
                ]
            );
            $this->logger->debug('Leaving '.$this->methodName);
        } else {
            $this->logger->debug('Exiting '.$this->methodName);
        }
    }
}
