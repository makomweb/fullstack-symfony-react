<?php

declare(strict_types=1);

namespace App\CloudEvent;

use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final readonly class CloudEvent
{
    /**
     * @see https://github.com/cloudevents/spec/blob/v1.0.2/cloudevents/spec.md#example
     */
    public function __construct(
        public string $specversion,
        public string $type,
        public string $source,
        public string $subject,
        public string $id,
        public string $time,
        public string $datacontenttype,
        public string $data,
    ) {
    }
}
