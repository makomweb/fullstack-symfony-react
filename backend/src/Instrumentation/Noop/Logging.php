<?php

declare(strict_types=1);

namespace App\Instrumentation\Noop;

use App\Game\Instrumentation\LoggingInterface;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
final readonly class Logging implements LoggingInterface
{
    public function info(string|\Stringable $message): void
    {
    }

    public function exception(\Throwable $ex): void
    {
    }
}
