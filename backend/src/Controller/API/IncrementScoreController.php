<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Controller\CommandFactory;
use App\Game\Command\CommandDescription;
use App\Game\Command\Executor\IncrementScoreCommandExecutor;
use App\Game\Command\IncrementScoreCommand;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api.')]
class IncrementScoreController extends CommandExecutingController
{
    public function __construct(
        CommandFactory $factory,
        IncrementScoreCommandExecutor $executor,
        CommandDescription $description,
    ) {
        parent::__construct($factory, $executor, $description);
    }

    #[Route('/games/{gameId}', name: 'increment_score', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Increment the score of the specified game',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['team', 'player_id'],
                properties: [
                    new OA\Property(
                        property: 'team',
                        type: 'string',
                        enum: ['guest', 'home'],
                        description: 'Which team scored the point'
                    ),
                    new OA\Property(
                        property: 'player_id',
                        type: 'integer',
                        description: 'The ID of the player who scored'
                    ),
                ],
                type: 'object'
            )
        ),
        parameters: [
            new OA\Parameter(
                name: 'gameId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
                description: 'ID of the game to update'
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 404, description: 'No such game'),
        ]
    )]
    #[IsGranted('increment_score')]
    public function incrementScore(string $gameId, Request $request): JsonResponse
    {
        $data = $request->toArray();
        $data['game_id'] = $gameId;

        return $this->execute(IncrementScoreCommand::class, $data);
    }
}
