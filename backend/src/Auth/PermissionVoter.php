<?php

declare(strict_types=1);

namespace App\Auth;

use App\Game\CurrentUserInterface;
use App\Instrumentation\DataCollector\PermissionVoterCollector;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string,object|null>
 */
final class PermissionVoter extends Voter
{
    public function __construct(
        private readonly CurrentUserInterface $currentUser,
        private readonly PermissionVoterCollector $collector,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            'index_games', 'add_game', 'increment_score', 'remove_game', 'show_statistics',
        ],
            strict: true
        );
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $hasPermission = $this->currentUser->hasPermission($attribute);

        $this->collector->addVoterCall($attribute, $subject, $hasPermission);

        return $hasPermission;
    }
}
