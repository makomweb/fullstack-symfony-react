<?php

declare(strict_types=1);

namespace App\Instrumentation\Otel;

use App\Game\Instrumentation\TraceInterface;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanBuilderInterface;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerProviderInterface as OtelTracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use OpenTelemetry\SemConv\TraceAttributes;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
final class TraceSpan implements TraceInterface
{
    private ?SpanInterface $span;

    /**
     * @param non-empty-string     $methodName
     * @param non-empty-string     $file
     * @param array<string,string> $context
     */
    public function __construct(
        private readonly OtelTracerProviderInterface $provider,
        private readonly string $methodName,
        private readonly string $file,
        private readonly array $context = [],
    ) {
        $this->span = null;
    }

    public function start(): void
    {
        $this->span = $this->getSpanBuilder()->startSpan();
    }

    public function recordException(\Throwable $ex): void
    {
        assert($this->span instanceof SpanInterface, 'You must call start before recording an exception!');

        $this->span->setStatus(StatusCode::STATUS_ERROR, $ex->getMessage());
        $this->span->recordException($ex, ['exception.escaped' => true]);
    }

    public function end(): void
    {
        assert($this->span instanceof SpanInterface, 'You must call start before calling end!');
        $this->span->end();

        assert($this->provider instanceof TracerProviderInterface);
        $this->provider->forceFlush();
    }

    private function getSpanBuilder(): SpanBuilderInterface
    {
        $tracer = $this->provider
            ->getTracer(__CLASS__, '0.0.1', 'https://opentelemetry.io/schemas/1.24.0');

        $builder = $tracer
            ->spanBuilder($this->methodName)
            ->setAttribute(TraceAttributes::CODE_FUNCTION_NAME, $this->methodName)
            ->setAttribute(TraceAttributes::CODE_FILEPATH, $this->file);

        if (!empty($this->context)) {
            $builder->setParent(
                TraceContextPropagator::getInstance()->extract($this->context)
            );
        } else {
            $scope = Context::storage()->scope();
            if (!is_null($scope)) {
                $builder->setParent(
                    $scope->context()
                );
            }
        }

        return $builder;
    }
}
