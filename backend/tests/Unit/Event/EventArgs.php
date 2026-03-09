<?php

declare(strict_types=1);

namespace App\Tests\Unit\Event;

readonly class EventArgs implements \Stringable
{
    public function __construct(public string $data)
    {
    }

    public function __toString(): string
    {
        return $this->data;
    }
}
