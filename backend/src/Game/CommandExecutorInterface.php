<?php

declare(strict_types=1);

namespace App\Game;

interface CommandExecutorInterface
{
    public function execute(CommandInterface $command): void;
}
