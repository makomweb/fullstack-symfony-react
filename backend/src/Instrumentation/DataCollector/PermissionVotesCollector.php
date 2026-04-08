<?php

declare(strict_types=1);

namespace App\Instrumentation\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class PermissionVotesCollector extends DataCollector
{
    public function addVoterCall(string $attribute, mixed $subject, bool $hasPermission): void
    {
        $this->data[] = [
            'attribute' => $attribute,
            'subject' => is_object($subject) ? get_class($subject) : gettype($subject),
            'hasPermission' => $hasPermission,
        ];
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        // Not required: addVoterCall() is used to collect manually.
    }

    /**
     * @return array{attribute: string, subject: string, hasPermission: bool}[]
     */
    public function getVoterCalls(): array
    {
        assert(is_array($this->data));

        /** @var array{attribute: string, subject: string, hasPermission: bool}[] */
        $calls = $this->data;

        return $calls;
    }

    /**
     * Gibt die Attribute und deren Häufigkeit für das Chart zurück.
     *
     * @return array<string, int>
     */
    public function getAttributeStats(): array
    {
        /**
         * @var array<string, int> $result
         */
        $result = array_reduce(
            $this->getVoterCalls(),
            /**
             * @param array<string, int>       $carry
             * @param array{attribute: string} $item
             *
             * @return array<string, int>
             */
            function (array $carry, array $item) {
                $attribute = $item['attribute'];
                if (!isset($carry[$attribute])) {
                    $carry[$attribute] = 0;
                }
                assert(is_int($carry[$attribute]));
                $carry[$attribute] = $carry[$attribute] + 1;

                return $carry;
            },
            []
        );

        return $result;
    }

    /**
     * Chart.js Labels (Attribute Namen).
     *
     * @return array<string>
     */
    public function getChartLabels(): array
    {
        return array_keys($this->getAttributeStats());
    }

    /**
     * Chart.js Data (Anzahl pro Attribut).
     *
     * @return array<int>
     */
    public function getChartData(): array
    {
        return array_values($this->getAttributeStats());
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function getName(): string
    {
        return 'app.permission_votes_collector';
    }
}
