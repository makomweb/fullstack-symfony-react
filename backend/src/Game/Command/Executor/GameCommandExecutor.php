<?php

declare(strict_types=1);

namespace App\Game\Command\Executor;

use App\Game\Command\CommandDescription;
use App\Game\Command\GameCommand;
use App\Game\CommandExecutorInterface;
use App\Game\CommandInterface;
use App\Game\Event;
use App\Game\EventStoreInterface;
use App\Game\Exception\GameException;
use App\Game\Instrumentation\TracingInterface;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
abstract class GameCommandExecutor implements CommandExecutorInterface
{
    public function __construct(
        private readonly EventFactory $factory,
        private readonly EventStoreInterface $eventStore,
        private readonly TracingInterface $tracing,
        private readonly CommandDescription $description,
        private readonly ProfilerInterface $profiler,
    ) {
    }

    /** Handle the command using the template method pattern. */
    public function execute(CommandInterface $command): void
    {
        $tracer = $this->tracing->createTracer(__METHOD__, __FILE__);

        $probe = $this->profiler->plantProbe(
            'command_executed',
            $this->description->for($command)
        );

        assert($command instanceof GameCommand);

        try {
            $this->beforeExecute($command);
            $event = $this->factory->fromCommand($command);
            $this->eventStore->persist($event);
            $this->afterExecute($command, $event);
        } catch (GameException $ex) {
            $tracer->recordException($ex);
            throw new ExecutionFailedException($ex->getMessage(), $ex->getCode(), $ex);
        } catch (\Throwable $ex) {
            $tracer->recordException($ex);
            throw new ExecutionFailedException(sprintf('%s has failed due to %s', $this->description->for($command), $ex->getMessage()), previous: $ex);
        }
    }

    /**
     * Logic which is executed before the command can be executed.
     * E.g. check preconditions.
     */
    abstract protected function beforeExecute(GameCommand $command): void;

    /**
     * Logic which is executed after the command was executed.
     * E.g. update cache or checking postconditions.
     */
    abstract protected function afterExecute(GameCommand $command, Event $event): void;
}
