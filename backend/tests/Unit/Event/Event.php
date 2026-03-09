<?php

declare(strict_types=1);

namespace App\Tests\Unit\Event;

class Event
{
    /** @var EventHandler[] */
    public array $handler = [];

    public function register(EventHandler $handler): void
    {
        if (!in_array($handler, $this->handler)) {
            $this->handler[] = $handler;
        }
    }

    public function unregister(EventHandler $handler): void
    {
        $this->handler = array_filter($this->handler, static fn (EventHandler $h) => !$h->isSame($handler));
    }

    public function raise(EventArgs $args): void
    {
        foreach ($this->handler as $handler) {
            $handler->onEvent($args);
        }
    }
}
