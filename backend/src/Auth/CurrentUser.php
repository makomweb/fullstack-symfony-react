<?php

declare(strict_types=1);

namespace App\Auth;

use App\Entity\User;
use App\Game\CurrentUserInterface;
use PHPMolecules\DDD\Attribute\Repository;
use Symfony\Bundle\SecurityBundle\Security;

#[Repository]
final readonly class CurrentUser implements CurrentUserInterface
{
    public function __construct(
        private Security $security,
        private Permissions $permissions,
    ) {
    }

    public function getUuid(): string
    {
        return $this
            ->getUser()
            ->getUuid()
            ->toString();
    }

    public function getEmail(): string
    {
        return $this
            ->getUser()
            ->getUserIdentifier();
    }

    public function hasPermission(string $permission): bool
    {
        return $this
            ->getUser()
            ->hasPermission($permission, $this->permissions);
    }

    private function getUser(): User
    {
        $user = $this->security->getUser();

        assert($user instanceof User, 'Please login first!');

        return $user;
    }
}
