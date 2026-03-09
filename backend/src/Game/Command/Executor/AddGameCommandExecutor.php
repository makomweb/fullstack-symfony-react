<?php

declare(strict_types=1);

namespace App\Game\Command\Executor;

use App\Game\Command\CommandDescription;
use App\Game\Command\GameCommand;
use App\Game\DenormalizerInterface;
use App\Game\Event;
use App\Game\EventStoreInterface;
use App\Game\Exception\GameAlreadyExistsException;
use App\Game\Game;
use App\Game\Games;
use App\Game\Instrumentation\LoggingInterface;
use App\Game\Instrumentation\TracingInterface;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
class AddGameCommandExecutor extends GameCommandExecutor
{
    public function __construct(
        EventFactory $factory,
        EventStoreInterface $eventStore,
        private readonly TracingInterface $tracing,
        private readonly LoggingInterface $logging,
        private readonly CommandDescription $description,
        private readonly Games $games,
        private readonly DenormalizerInterface $denormalizer,
        ProfilerInterface $profiler,
    ) {
        parent::__construct($factory, $eventStore, $tracing, $description, $profiler);
    }

    protected function beforeExecute(GameCommand $command): void
    {
        $tracer = $this->tracing->createTracer(__METHOD__, __FILE__);

        if ($this->games->exists($command->gameId)) {
            throw new GameAlreadyExistsException($command->gameId);
        }
    }

    protected function afterExecute(GameCommand $command, Event $event): void
    {
        $tracer = $this->tracing->createTracer(__METHOD__, __FILE__);

        $game = $this->denormalizer->fromArray($event->payload, Game::class);
        assert($game instanceof Game);

        $this->games->add($game);

        $this->logging->info($this->description->successMessageFor($command));
        $this->logging->info(sprintf('🛎️ New game: %s', $game->getShortName()));
    }
}
