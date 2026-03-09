<?php

declare(strict_types=1);

namespace App\Controller\DTO;

use PHPMolecules\DDD\Attribute\ValueObject;
use Symfony\Component\Validator\Constraints as Assert;

#[ValueObject]
final class IncrementScore implements DtoInterface
{
    public const TEAMS = ['guest', 'home'];

    public function __construct(
        #[Assert\NotBlank]
        public string $gameId,
        #[Assert\Choice(choices: self::TEAMS, message: 'The team {{ value }} is invalid.')]
        public readonly string $team,
        #[Assert\PositiveOrZero(message: 'The player id {{ value }} is invalid.')]
        public readonly int $playerId,
        public readonly \DateTimeImmutable $timestamp = new \DateTimeImmutable(),
    ) {
    }
}
