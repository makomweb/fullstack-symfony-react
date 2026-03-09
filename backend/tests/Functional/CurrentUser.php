<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Game\CurrentUserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

final class CurrentUser implements CurrentUserInterface
{
    private Uuid $uuid;

    public function __construct(private string $email)
    {
        $this->uuid = new UuidV7();
    }

    public function getUuid(): string
    {
        return $this->uuid->toString();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function hasPermission(string $permission): bool
    {
        return true;
    }
}
