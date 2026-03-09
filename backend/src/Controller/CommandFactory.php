<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\DTO\AddGame;
use App\Controller\DTO\DtoInterface;
use App\Controller\DTO\IncrementScore;
use App\Controller\DTO\RemoveGame;
use App\Controller\DTO\ValidationFailedException;
use App\Game\Command\AddGameCommand;
use App\Game\Command\IncrementScoreCommand;
use App\Game\Command\RemoveGameCommand;
use App\Game\CommandInterface;
use AutoMapper\AutoMapper;
use PHPMolecules\DDD\Attribute\Factory;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * An instance of this class encapsulates
 * validation with the creation of a strongly typed command object.
 */
#[Factory]
final readonly class CommandFactory
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * Create a DTO from the specified data and validate it.
     * Once validated, map the DTO to a command object.
     *
     * @param class-string $commandType the expected type of the command
     * @param mixed        $data        the data the command consists of
     */
    public function create(string $commandType, mixed $data): CommandInterface
    {
        /** @var DtoInterface $dto */
        $dto = $this->denormalizer->denormalize(
            $data,
            // Find the DTO type for the specified command type:
            match ($commandType) {
                AddGameCommand::class => AddGame::class,
                IncrementScoreCommand::class => IncrementScore::class,
                RemoveGameCommand::class => RemoveGame::class,
                default => throw new \InvalidArgumentException(sprintf('There is no DTO type for the specified command type "%s"!', $commandType)),
            }
        );

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            throw new ValidationFailedException($dto, $violations);
        }

        $command = AutoMapper::create()
            ->map($dto, $commandType);

        assert($command instanceof CommandInterface);

        return $command;
    }
}
