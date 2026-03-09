<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Game\Command\AddGameCommand;
use App\Game\Command\CommandDescription;
use App\Game\Command\Executor\AddGameCommandExecutor;
use App\Game\Command\Executor\EventFactory;
use App\Game\Command\Executor\ExecutionFailedException;
use App\Game\Command\Executor\GameCommandExecutor;
use App\Game\Command\Executor\IncrementScoreCommandExecutor;
use App\Game\Command\Executor\ProfilerInterface;
use App\Game\Command\Executor\RemoveGameCommandExecutor;
use App\Game\Command\GameCommand;
use App\Game\Command\IncrementScoreCommand;
use App\Game\Command\RemoveGameCommand;
use App\Game\CurrentUserInterface;
use App\Game\Event;
use App\Game\EventStoreInterface;
use App\Game\Exception\GameAlreadyExistsException;
use App\Game\Exception\GameDoesNotExistException;
use App\Game\Game;
use App\Game\Games;
use App\Game\Instrumentation\TracingInterface;
use App\Game\Statistic;
use App\Game\Statistics;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GamesTest extends KernelTestCase
{
    public function setUp(): void
    {
        self::bootKernel(['environment' => 'test']);
        self::getContainer()->set(EventStoreInterface::class, new InMemoryEventStore());
        $repository = self::getContainer()->get(EventStoreInterface::class);
        assert($repository instanceof EventStoreInterface);
        $repository->reset();

        self::getContainer()->set(CurrentUserInterface::class, new CurrentUser('foo@bar.com'));
    }

    #[Test]
    public function adding_game_twice_should_fail(): void
    {
        $games = self::getContainer()->get(Games::class);
        assert($games instanceof Games);
        $game = new Game(new \DateTimeImmutable(), 'A', 'B');
        $games->add($game);
        $this->expectException(GameAlreadyExistsException::class);
        $games->add($game);
    }

    #[Test]
    public function removing_should_fail_when_no_such_game(): void
    {
        $games = self::getContainer()->get(Games::class);
        assert($games instanceof Games);
        $this->expectException(GameDoesNotExistException::class);
        $games->remove('non-existent-id');
    }

    #[Test]
    public function getting_game_details_should_work(): void
    {
        $eventStore = self::getContainer()->get(EventStoreInterface::class);
        assert($eventStore instanceof EventStoreInterface);

        $addGame = self::getContainer()->get(AddGameCommandExecutor::class);
        assert($addGame instanceof AddGameCommandExecutor);

        $game = new Game(new \DateTimeImmutable(), 'AB', 'CD');
        $addGame->execute(new AddGameCommand($game->dateTime, $game->home, $game->guest));

        self::assertCount(1, $eventStore->getEvents(), 'There should be 1 event!');
        self::assertCount(1, self::indexGames(), 'There should be 1 game!');

        $incrementScore = self::getContainer()->get(IncrementScoreCommandExecutor::class);
        assert($incrementScore instanceof IncrementScoreCommandExecutor);

        /** @var non-empty-string $gameId */
        $gameId = $game->getId();
        $incrementScore->execute(new IncrementScoreCommand($gameId, 'home', 23));
        $incrementScore->execute(new IncrementScoreCommand($gameId, 'guest', 77));

        self::assertCount(3, $eventStore->getEvents(), 'There should be 3 events!');

        $game = self::indexGames()[0];
        $statistics = self::getStatistic($game);

        self::assertEquals('AB', $game->home);
        self::assertSame(1, $statistics->homePoints);

        self::assertEquals('CD', $game->guest);
        self::assertSame(1, $statistics->guestPoints);

        self::assertStringContainsString('AB vs CD - 1 : 1', (string) $statistics);
    }

    #[Test]
    public function adding_game_should_work(): void
    {
        $eventStore = self::getContainer()->get(EventStoreInterface::class);
        assert($eventStore instanceof EventStoreInterface);

        self::assertCount(0, $eventStore->getEvents(), 'There should be no events!');
        self::assertCount(0, self::indexGames(), 'There should be no games!');

        $addGame = self::getContainer()->get(AddGameCommandExecutor::class);
        assert($addGame instanceof AddGameCommandExecutor);
        $addGame->execute(
            new AddGameCommand(
                new \DateTimeImmutable(),
                'RB Leipzig',
                'Union Berlin'
            )
        );

        self::assertCount(1, $eventStore->getEvents(), 'There should be 1 event!');
        self::assertCount(1, self::indexGames(), 'There should be 1 game!');
    }

    #[Test]
    public function adding_a_game_twice_should_fail(): void
    {
        $eventStore = self::getContainer()->get(EventStoreInterface::class);
        assert($eventStore instanceof EventStoreInterface);

        self::assertCount(0, $eventStore->getEvents(), 'There should be no events!');
        self::assertCount(0, self::indexGames(), 'There should be no games!');

        $addGame = self::getContainer()->get(AddGameCommandExecutor::class);
        assert($addGame instanceof AddGameCommandExecutor);

        $command = new AddGameCommand(new \DateTimeImmutable('2024-01-28'), 'RB Leipzig', 'Union Berlin');
        $addGame->execute($command);

        try {
            $addGame->execute($command);
            self::fail('Should have thrown before!');
        } catch (ExecutionFailedException $ex) {
            $pattern = '/(?:Adding game has failed due to 🚩 )?There is already a game with ID "[a-f0-9]{64}"!/';
            self::assertEquals(1, preg_match($pattern, $ex->getMessage()));
        }
    }

    #[Test]
    public function tracking_score_should_work(): void
    {
        $eventStore = self::getContainer()->get(EventStoreInterface::class);
        assert($eventStore instanceof EventStoreInterface);

        self::assertCount(0, $eventStore->getEvents(), 'There should be no events!');
        self::assertCount(0, self::indexGames(), 'There should be no games!');

        $game = new Game(new \DateTimeImmutable(), 'RB Leipzig', 'Union Berlin');
        $addGame = self::getContainer()->get(AddGameCommandExecutor::class);
        assert($addGame instanceof AddGameCommandExecutor);
        $addGame->execute(new AddGameCommand($game->dateTime, $game->home, $game->guest));

        $incrementScore = self::getContainer()->get(IncrementScoreCommandExecutor::class);
        assert($incrementScore instanceof IncrementScoreCommandExecutor);

        /** @var non-empty-string $gameId */
        $gameId = $game->getId();
        $incrementScore->execute(new IncrementScoreCommand($gameId, 'home', 23));
        $events = $eventStore->getEvents();

        self::assertCount(2, $events, 'There should be 2 events!');
        self::assertSame('added', $events[0]->eventType);
        self::assertSame('scored', $events[1]->eventType);

        $statistics = self::getContainer()->get(Statistics::class);
        assert($statistics instanceof Statistics);
        $statistic = $statistics->getStatistic($game, forceCacheRefresh: true);

        self::assertEquals(1, $statistic->homePoints);
    }

    #[Test]
    public function tracking_score_for_unknown_game_should_fail(): void
    {
        $trackScore = self::getContainer()->get(IncrementScoreCommandExecutor::class);
        assert($trackScore instanceof IncrementScoreCommandExecutor);

        $command = new IncrementScoreCommand('some-random-game-id', 'home', 33);

        $this->expectException(ExecutionFailedException::class);
        $this->expectExceptionMessage('🚩 There is no game with ID "some-random-game-id"!');

        $trackScore->execute($command);
    }

    #[Test]
    public function statistics_for_unknown_game_should_be_initial(): void
    {
        $unknownGame = new Game(new \DateTimeImmutable(), 'Ravens', 'Ospreys');

        $statistic = self::getStatistic($unknownGame);

        self::assertEquals(0, $statistic->homePoints);
        self::assertEquals(0, $statistic->guestPoints);
        self::assertTrue(str_contains($statistic->gameName, 'Ravens vs Ospreys'));
    }

    #[Test]
    public function remove_game_should_work(): void
    {
        $eventStore = self::getContainer()->get(EventStoreInterface::class);
        assert($eventStore instanceof EventStoreInterface);
        self::assertCount(0, self::indexGames(), 'There should be no games!');

        $addGame = self::getContainer()->get(AddGameCommandExecutor::class);
        assert($addGame instanceof AddGameCommandExecutor);
        $addGame->execute(
            new AddGameCommand(
                new \DateTimeImmutable(),
                'RB Leipzig',
                'Union Berlin'
            )
        );

        $games = self::indexGames();
        self::assertCount(1, $games, 'There should be 1 game!');
        $game = $games[0];

        $removeGame = self::getContainer()->get(RemoveGameCommandExecutor::class);
        assert($removeGame instanceof RemoveGameCommandExecutor);
        $removeGame->execute(new RemoveGameCommand($game->getId()));

        $games = self::indexGames();
        self::assertEmpty($games, 'There should be no games!');
    }

    #[Test]
    public function remove_not_present_game_should_fail(): void
    {
        $removeGame = self::getContainer()->get(RemoveGameCommandExecutor::class);
        assert($removeGame instanceof RemoveGameCommandExecutor);

        $this->expectException(ExecutionFailedException::class);
        $removeGame->execute(new RemoveGameCommand('Invalid-Game-ID'));
    }

    #[Test]
    #[DataProvider('provideTestData')]
    public function executor_throws(bool $beforeExecuteThrows, bool $afterExecuteThrows, string $expectedMessage): void
    {
        $executor = self::createThrowingExecutor($beforeExecuteThrows, $afterExecuteThrows);

        $this->expectException(ExecutionFailedException::class);
        $this->expectExceptionMessage($expectedMessage);

        $game = new Game(new \DateTimeImmutable(), 'AB', 'CD');
        $executor->execute(new AddGameCommand($game->dateTime, $game->home, $game->guest));
    }

    /** @return array<int, mixed> */
    public static function provideTestData(): array
    {
        return [
            [true, false, 'Adding game has failed due to Flash! ⚡️'],
            [false, true, 'Adding game has failed due to Boom! 💥'],
            [true, true, 'Adding game has failed due to Flash! ⚡️'],
        ];
    }

    private static function createThrowingExecutor(bool $throwsBeforeExecute, bool $throwsAfterExecute): GameCommandExecutor
    {
        $factory = self::getContainer()->get(EventFactory::class);
        assert($factory instanceof EventFactory);

        $eventStore = self::getContainer()->get(EventStoreInterface::class);
        assert($eventStore instanceof EventStoreInterface);

        $tracing = self::getContainer()->get(TracingInterface::class);
        assert($tracing instanceof TracingInterface);

        $description = self::getContainer()->get(CommandDescription::class);
        assert($description instanceof CommandDescription);

        $profiler = self::getContainer()->get(ProfilerInterface::class);
        assert($profiler instanceof ProfilerInterface);

        return new class($factory, $eventStore, $tracing, $description, $profiler, $throwsBeforeExecute, $throwsAfterExecute) extends GameCommandExecutor {
            public function __construct(
                EventFactory $factory,
                EventStoreInterface $eventStore,
                TracingInterface $tracing,
                CommandDescription $description,
                ProfilerInterface $profiler,
                private readonly bool $throwsBeforeExecute,
                private readonly bool $throwsAfterExecute,
            ) {
                parent::__construct($factory, $eventStore, $tracing, $description, $profiler);
            }

            protected function beforeExecute(GameCommand $command): void
            {
                if ($this->throwsBeforeExecute) {
                    throw new \Exception('Flash! ⚡️');
                }
            }

            protected function afterExecute(GameCommand $command, Event $event): void
            {
                if ($this->throwsAfterExecute) {
                    throw new \Exception('Boom! 💥');
                }
            }
        };
    }

    /** @return Game[] */
    private static function indexGames(): array
    {
        $games = self::getContainer()->get(Games::class);
        assert($games instanceof Games);

        return $games->index(
            /* The cache might be outdated. */
            forceCacheRefresh: true
        );
    }

    private static function getStatistic(Game $game): Statistic
    {
        $service = self::getContainer()->get(Statistics::class);
        assert($service instanceof Statistics);

        return $service->getStatistic($game);
    }
}
