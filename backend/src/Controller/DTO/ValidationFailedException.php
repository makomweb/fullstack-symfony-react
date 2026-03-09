<?php

declare(strict_types=1);

namespace App\Controller\DTO;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationFailedException extends \Exception
{
    public function __construct(
        public readonly DtoInterface $dto,
        public readonly ConstraintViolationListInterface $violations,
    ) {
        parent::__construct(
            self::createMessage($dto, $violations),
            400 // Bad Request
        );
    }

    private static function createMessage(DtoInterface $dto, ConstraintViolationListInterface $violations): string
    {
        $shortName = (new \ReflectionClass($dto))->getShortName();

        return $shortName.' is invalid. '.implode(' ', iterator_to_array(self::yieldMessages($violations)));
    }

    /**
     * @return \Generator<string>
     */
    private static function yieldMessages(ConstraintViolationListInterface $violations): \Generator
    {
        foreach ($violations as $violation) {
            $message = $violation->getMessage();
            assert(is_string($message));
            yield $message;
        }
    }
}
