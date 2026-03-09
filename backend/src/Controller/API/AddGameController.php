<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Controller\CommandFactory;
use App\Game\Command\AddGameCommand;
use App\Game\Command\CommandDescription;
use App\Game\Command\Executor\AddGameCommandExecutor;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api.')]
class AddGameController extends CommandExecutingController
{
    public function __construct(
        CommandFactory $factory,
        AddGameCommandExecutor $executor,
        CommandDescription $description,
    ) {
        parent::__construct($factory, $executor, $description);
    }

    #[Route('/games', name: 'add_game', methods: ['POST'])]
    #[OA\Post(
        summary: 'Add a game',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['guest', 'home', 'date_time'],
                properties: [
                    new OA\Property(
                        property: 'guest',
                        type: 'string',
                        description: 'Name of the guest team'
                    ),
                    new OA\Property(
                        property: 'home',
                        type: 'string',
                        description: 'Name of the home team'
                    ),
                    new OA\Property(
                        property: 'date_time',
                        type: 'string',
                        format: 'date-time',
                        description: 'Scheduled date and time of the game (ISO 8601)'
                    ),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Game successfully added'
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input data'
            ),
        ]
    )]
    #[IsGranted('add_game')]
    public function addGame(Request $request): JsonResponse
    {
        $data = $request->toArray();

        return $this->execute(AddGameCommand::class, $data);
    }
}
