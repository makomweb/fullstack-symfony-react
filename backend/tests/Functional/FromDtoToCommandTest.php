<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Controller\DTO\AddGame;
use App\Game\Command\AddGameCommand;
use AutoMapper\AutoMapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FromDtoToCommandTest extends TestCase
{
    #[Test]
    public function from_dto(): void
    {
        $dto = new AddGame(new \DateTimeImmutable(), 'Union', 'Hertha');

        $mapper = AutoMapper::create();

        $command = $mapper->map($dto, AddGameCommand::class);
        assert($command instanceof AddGameCommand);

        self::assertEquals($dto->dateTime, $command->dateTime);
        self::assertEquals($dto->home, $command->home);
        self::assertEquals($dto->guest, $command->guest);
    }
}
