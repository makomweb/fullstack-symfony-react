<?php

declare(strict_types=1);

namespace App\Tests\Unit\Event;

readonly class EventHandler
{
    protected string $id;

    public function __construct()
    {
        $this->id = bin2hex(random_bytes(10));
    }

    public function onEvent(EventArgs $args): void
    {
        // TODO Implement handling logic here!
    }

    public function isSame(EventHandler $other): bool
    {
        return $this->id == $other->id;
    }
}
