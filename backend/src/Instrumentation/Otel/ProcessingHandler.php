<?php

declare(strict_types=1);

namespace App\Instrumentation\Otel;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord as MonologLogRecord;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Logs\LoggerInterface as OtelLoggerInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface as OtelLoggerProviderInterface;
use OpenTelemetry\API\Logs\LogRecord as OpenTelemetryLogRecord;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
final class ProcessingHandler extends AbstractProcessingHandler
{
    private OtelLoggerProviderInterface $provider;

    public function __construct(Level $level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->provider = Globals::loggerProvider();
    }

    protected function write(MonologLogRecord $record): void
    {
        $this->getLogger()->emit(
            (new OpenTelemetryLogRecord($record->message))
                ->setAttribute('channel', $record->channel)
                ->setAttribute('level', $record->level->value)
                ->setAttribute('context', $record->context)
        );

        assert($this->provider instanceof LoggerProviderInterface);
        $this->provider->forceFlush();
    }

    private function getLogger(): OtelLoggerInterface
    {
        return $this->provider->getLogger('my-logger');
    }
}
