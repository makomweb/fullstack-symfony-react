<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Game\Exception\GameAlreadyExistsException;
use App\Game\Exception\GameDoesNotExistException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GameExceptionTest extends TestCase
{
    #[Test]
    public function game_does_not_exist(): void
    {
        $ex = new GameDoesNotExistException('test');

        self::assertEquals('🚩 There is no game with ID "test"!', $ex->getMessage());
    }

    #[Test]
    public function game_exists(): void
    {
        $ex = new GameAlreadyExistsException('test');

        self::assertEquals('🚩 There is already a game with ID "test"!', $ex->getMessage());
    }
}
