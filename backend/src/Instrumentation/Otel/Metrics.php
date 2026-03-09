<?php

declare(strict_types=1);

namespace App\Instrumentation\Otel;

use App\Game\Instrumentation\MetricsInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Metrics\MeterInterface as OtelMeter;
use OpenTelemetry\API\Metrics\MeterProviderInterface as OtelMeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface as SdkMeterProviderInterface;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
final class Metrics implements MetricsInterface
{
    private OtelMeterProvider $meterProvider;

    public function __construct()
    {
        $this->meterProvider = Globals::meterProvider();
    }

    public function recordMessage(string $name, string $message): void
    {
        $this
            ->getMeter()
            ->createCounter($name)
            ->add(1, ['message' => $message]);

        $this->flush();
    }

    public function record(string $name, float|int $value, string $unit): void
    {
        $this
            ->getMeter()
            ->createGauge($name, $unit)
            ->record($value);

        $this->flush();
    }

    private function getMeter(): OtelMeter
    {
        return $this->meterProvider->getMeter(__CLASS__, '0.0.1', 'https://opentelemetry.io/schemas/1.24.0');
    }

    private function flush(): void
    {
        assert($this->meterProvider instanceof SdkMeterProviderInterface);
        $this->meterProvider->forceFlush();
    }
}
