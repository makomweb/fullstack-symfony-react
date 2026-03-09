<?php

declare(strict_types=1);

namespace App\Instrumentation\Stdout;

use Monolog\Formatter\LineFormatter;
use PHPMolecules\DDD\Attribute\Service;

#[Service]
class CustomLineFormatter extends LineFormatter
{
    public function __construct()
    {
        $timestampFormat = 'd-M-Y H:i:s'; // Format: 03-Mar-2026 10:27:23
        $format = "[%datetime%] %level_name%: %message%\n";
        parent::__construct($format, $timestampFormat, false, true);
    }
}
