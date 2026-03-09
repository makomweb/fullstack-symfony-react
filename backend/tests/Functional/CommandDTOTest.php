<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Controller\DTO\AddGame;
use App\Controller\DTO\DtoInterface;
use App\Controller\DTO\IncrementScore;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CommandDTOTest extends KernelTestCase
{
    public function setUp(): void
    {
        self::bootKernel(['environment' => 'test']);
    }

    #[Test]
    public function create_add_game_should_work(): void
    {
        $command = self::createDTO(AddGame::class, [
            'date_time' => '2024-12-22T11:30:03+00:00',
            'home' => 'foo',
            'guest' => 'bar',
        ]);

        self::assertInstanceOf(AddGame::class, $command);
        self::assertEquals('foo', $command->home);
        self::assertEquals('bar', $command->guest);
    }

    #[Test]
    public function create_increment_score_command_should_work(): void
    {
        $command = self::createDTO(IncrementScore::class, [
            'game_id' => 'some-random-id',
            'team' => 'home',
            'player_id' => 45,
        ]);

        self::assertInstanceOf(IncrementScore::class, $command);
        self::assertEquals('some-random-id', $command->gameId);
        self::assertEquals('home', $command->team);
        self::assertEquals(45, $command->playerId);
    }

    private static function createDTO(string $dtoType, mixed $data): DtoInterface
    {
        $denormalizer = self::getContainer()->get(DenormalizerInterface::class);
        assert($denormalizer instanceof DenormalizerInterface);

        $dto = $denormalizer->denormalize($data, $dtoType);

        assert($dto instanceof DtoInterface);

        return $dto;
    }
}
