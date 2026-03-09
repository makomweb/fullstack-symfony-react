<?php

declare(strict_types=1);

namespace App\Listener;

use App\Game\Instrumentation\LoggingInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

#[AsEventListener(event: ExceptionEvent::class)]
final readonly class ErrorLogger
{
    public function __construct(private LoggingInterface $logging)
    {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $this->logging->exception($exception);
    }
}
