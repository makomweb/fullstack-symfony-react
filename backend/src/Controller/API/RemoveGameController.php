<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Controller\CommandFactory;
use App\Game\Command\CommandDescription;
use App\Game\Command\Executor\RemoveGameCommandExecutor;
use App\Game\Command\RemoveGameCommand;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api.')]
class RemoveGameController extends CommandExecutingController
{
    public function __construct(
        CommandFactory $factory,
        RemoveGameCommandExecutor $executor,
        CommandDescription $description,
    ) {
        parent::__construct($factory, $executor, $description);
    }

    #[Route('/games/{gameId}', name: 'remove_game', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Remove the specified game')]
    #[IsGranted('remove_game')]
    public function removeGame(string $gameId): JsonResponse
    {
        return $this->execute(RemoveGameCommand::class, ['game_id' => $gameId]);
    }
}
