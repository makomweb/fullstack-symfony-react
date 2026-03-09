<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Game\DenormalizerInterface;
use App\Game\Event;
use App\Game\Games;
use App\Game\GamesReducer;
use App\Invariant\InvariantException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GamesReducerTest extends TestCase
{
    #[Test]
    public function unknown_event_should_throw(): void
    {
        $denormalizer = $this->createStub(DenormalizerInterface::class);
        $reducer = new GamesReducer($denormalizer);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('Event type "unknown-type" is not supported!');
        $reducer->__invoke([], new Event('Games', Games::UNIQUE_ID, 'unknown-type', []));
    }
}
