<?php

declare(strict_types=1);

namespace App\Controller\DTO;

use PHPMolecules\DDD\Attribute\ValueObject;
use Symfony\Component\Validator\Constraints as Assert;

#[ValueObject]
final class RemoveGame implements DtoInterface
{
    public function __construct(
        #[Assert\NotBlank]
        public string $gameId,
    ) {
    }
}
