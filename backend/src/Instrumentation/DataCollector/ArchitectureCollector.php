<?php

declare(strict_types=1);

namespace App\Instrumentation\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ArchitectureCollector extends DataCollector
{
    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
    }

    public function getName(): string
    {
        return 'app.architecture_collector';
    }

    public function getDetails(): array
    {
        return [];
    }
}
