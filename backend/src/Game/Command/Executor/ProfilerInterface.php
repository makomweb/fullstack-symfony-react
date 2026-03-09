<?php

declare(strict_types=1);

namespace App\Game\Command\Executor;

interface ProfilerInterface
{
    public function plantProbe(string $id, string $description): ProbeInterface;
}
