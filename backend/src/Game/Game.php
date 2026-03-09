<?php

declare(strict_types=1);

namespace App\Game;

use PHPMolecules\DDD\Attribute\Entity;

#[Entity]
final readonly class Game
{
    /**
     * @param non-empty-string $home
     * @param non-empty-string $guest
     */
    public function __construct(
        public \DateTimeImmutable $dateTime,
        public string $home,
        public string $guest,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getId(): string
    {
        return GameId::create($this->getName());
    }

    public function getName(): string
    {
        return GameName::create(
            $this->dateTime,
            $this->home,
            $this->guest
        );
    }

    public function getShortName(): string
    {
        return sprintf('%s vs %s', $this->home, $this->guest);
    }
}
