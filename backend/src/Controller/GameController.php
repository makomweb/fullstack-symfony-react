<?php

namespace App\Controller;

use App\Game\Command\AddGameCommand;
use App\Game\Command\Executor\AddGameCommandExecutor;
use App\Game\Command\Executor\IncrementScoreCommandExecutor;
use App\Game\Command\Executor\RemoveGameCommandExecutor;
use App\Game\Command\IncrementScoreCommand;
use App\Game\Command\RemoveGameCommand;
use App\Game\Game;
use App\Game\Games;
use App\Game\Statistics;
use Faker\Factory as Faker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/game', name: 'app.')]
final class GameController extends AbstractController
{
    public function __construct(
        private readonly Games $games,
        private readonly Statistics $statistics,
        private readonly CommandFactory $factory,
        private readonly AddGameCommandExecutor $addGame,
        private readonly RemoveGameCommandExecutor $removeGame,
        private readonly IncrementScoreCommandExecutor $incrementScore,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route('/index', name: 'index_games', methods: ['GET'])]
    #[IsGranted('index_games')]
    #[IsGranted('show_statistics')]
    public function index(): Response
    {
        // Equip game objects with its statistic:
        $games = array_map(
            fn (Game $game) => [
                'id' => $game->getId(),
                'dateTime' => $game->dateTime,
                'name' => $game->getShortName(),
                'stats' => $this
                    ->statistics
                    ->getStatistic($game, forceCacheRefresh: false)
                    ->getPointsAsString(),
            ],
            $this->games->index(forceCacheRefresh: false)
        );

        return $this->render('game/index.html.twig', [
            'games' => $games,
        ]);
    }

    #[Route('/add', name: 'add_game', methods: ['POST'])]
    #[IsGranted('add_game')]
    public function add(Request $request): Response
    {
        $submittedToken = $request->request->get('_token');
        assert(is_string($submittedToken));

        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('add_game', $submittedToken))) {
            throw new AccessDeniedException('Invalid CSRF token');
        }

        $faker = Faker::create();

        $command = $this->factory->create(AddGameCommand::class, [
            'date_time' => (new \DateTimeImmutable())->format(\DateTime::ATOM),
            'home' => $faker->company(),
            'guest' => $faker->company(),
        ]);

        $this->addGame->execute($command);

        return $this->redirectToRoute('app.index_games');
    }

    #[Route('/remove', name: 'remove_game', methods: ['POST'])]
    #[IsGranted('remove_game')]
    public function remove(Request $request): Response
    {
        /** @var non-empty-string $gameId */
        $gameId = $request->request->get('gameId');

        $submittedToken = $request->request->get('_token');
        assert(is_string($submittedToken));

        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('remove_game'.$gameId, $submittedToken))) {
            throw new AccessDeniedException('Invalid CSRF token');
        }

        $command = $this->factory->create(RemoveGameCommand::class, [
            'game_id' => $gameId,
        ]);

        $this->removeGame->execute($command);

        return $this->redirectToRoute('app.index_games');
    }

    #[Route('/increment', name: 'increment_score', methods: ['POST', 'PUT'])]
    #[IsGranted('increment_score')]
    public function increment(Request $request): Response
    {
        /** @var non-empty-string $gameId */
        $gameId = $request->request->get('gameId');

        /** @var non-empty-string $team */
        $team = $request->request->get('team');

        $submittedToken = $request->request->get('_token');
        assert(is_string($submittedToken));

        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('increment_score'.$gameId.$team, $submittedToken))) {
            throw new AccessDeniedException('Invalid CSRF token');
        }

        $command = $this->factory->create(IncrementScoreCommand::class, [
            'game_id' => $gameId,
            'team' => $team,
            'player_id' => 33,
        ]);

        $this->incrementScore->execute($command);

        return $this->redirectToRoute('app.index_games');
    }
}
