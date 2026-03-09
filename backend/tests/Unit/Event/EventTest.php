<?php

declare(strict_types=1);

namespace App\Tests\Unit\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    #[Test]
    public function event_handler_is_executed(): void
    {
        $event = new Event();

        $h1 = new EventHandler();
        $h2 = new EventHandler();
        $h3 = new EventHandler();
        $h4 = new EventHandler();

        $event->register($h1);
        $event->register($h2);
        $event->register($h3);
        $event->register($h4);
        $event->register($h4);

        self::assertCount(4, $event->handler);

        $event->unregister($h3);

        self::assertCount(3, $event->handler);

        $event->raise(new EventArgs('foobar'));
    }
}
