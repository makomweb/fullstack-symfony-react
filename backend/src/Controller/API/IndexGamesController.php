<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Game\Games;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api.')]
class IndexGamesController extends AbstractController
{
    public function __construct(
        private readonly Games $games,
    ) {
    }

    #[Route('/games', name: 'index_games', methods: ['GET'])]
    #[OA\Get(summary: 'Index of games')]
    #[IsGranted('index_games')]
    public function indexGames(
        #[MapQueryParameter('from_cache')] bool $fromCache = true,
    ): JsonResponse {
        $games = $this->games->index(forceCacheRefresh: !$fromCache);

        return $this->json($games);
    }
}
