<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Controller\CommandFactory;
use App\Game\Command\CommandDescription;
use App\Game\Command\GameCommand;
use App\Game\CommandExecutorInterface;
use App\Game\CommandInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class CommandExecutingController extends AbstractController
{
    protected function __construct(
        private readonly CommandFactory $factory,
        private readonly CommandExecutorInterface $executor,
        private readonly CommandDescription $description,
    ) {
    }

    /**
     * @param class-string $commandType the expected type of the command
     * @param mixed        $data        the data the command consists of
     */
    protected function execute(string $commandType, mixed $data): JsonResponse
    {
        $command = $this->factory->create($commandType, $data);

        $this->executor->execute($command);

        return $this->createResponse($command);
    }

    private function createResponse(CommandInterface $command): JsonResponse
    {
        assert(
            $command instanceof GameCommand,
            new \InvalidArgumentException(sprintf('Class %s is not supported!', get_debug_type($command)))
        );

        return $this->json(
            [
                'message' => $this->description->successMessageFor($command),
                'game_id' => $command->gameId,
            ],
            Response::HTTP_OK
        );
    }
}
