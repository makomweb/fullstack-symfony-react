<?php

declare(strict_types=1);

namespace App\Controller\DTO;

use PHPMolecules\DDD\Attribute\ValueObject;
use Symfony\Component\Validator\Constraints as Assert;

#[ValueObject]
final class AddGame implements DtoInterface
{
    public function __construct(
        public readonly \DateTimeImmutable $dateTime,
        #[Assert\NotBlank]
        public readonly string $home,
        #[Assert\NotBlank]
        public readonly string $guest,
    ) {
    }
}
