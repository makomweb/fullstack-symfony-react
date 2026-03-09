<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Game\CacheInterface;
use App\Game\DenormalizerInterface;
use App\Game\EventStoreInterface;
use App\Game\Exception\GameDoesNotExistException;
use App\Game\Game;
use App\Game\Games;
use App\Game\GamesReducer;
use App\Instrumentation\Noop\Logging;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GamesTest extends TestCase
{
    #[Test]
    public function there_should_be2_games(): void
    {
        $games = $this->createGames([
            new Game(new \DateTimeImmutable(), 'AB', 'CD'),
            new Game(new \DateTimeImmutable(), 'XY', 'ZA'),
        ]);

        $index = $games->index();

        self::assertCount(2, $index, 'There should be 2 games!');
    }

    #[Test]
    public function could_not_find_game_should_throw(): void
    {
        $games = $this->createGames([/* No games. */]);

        $this->expectException(GameDoesNotExistException::class);
        $this->expectExceptionMessage('🚩 There is no game with ID "arbitrary-game-id"!');
        /* $game = */ $games->find('arbitrary-game-id');
    }

    /**
     * @param Game[] $games
     */
    private function createGames(array $games): Games
    {
        $cache = $this->createStub(CacheInterface::class);
        $cache
            ->method('getValue')
            ->willReturn($games);

        $eventStore = $this->createStub(EventStoreInterface::class);

        $converter = $this->createStub(DenormalizerInterface::class);

        return new Games(
            $cache,
            $eventStore,
            new GamesReducer($converter),
            new Logging()
        );
    }
}
