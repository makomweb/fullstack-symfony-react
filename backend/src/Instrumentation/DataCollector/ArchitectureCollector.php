<?php

declare(strict_types=1);

namespace App\Instrumentation\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\Yaml\Yaml;

class ArchitectureCollector extends DataCollector
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $this->data = $this->parseDeptracConfig();
    }

    public function getName(): string
    {
        return 'app.architecture_collector';
    }

    /**
     * @return array{layers: array<string, array{name: string, position: int}>, dependencies: array<string, array<string>>}
     */
    public function getDetails(): array
    {
        /** @var array{layers: array<string, array{name: string, position: int}>, dependencies: array<string, array<string>>} $data */
        $data = $this->data;

        return $data;
    }

    /**
     * @return array{layers: array<string, array{name: string, position: int}>, dependencies: array<string, array<string>>}
     */
    private function parseDeptracConfig(): array
    {
        $deptracPath = $this->projectDir.'/deptrac.yaml';

        if (!file_exists($deptracPath)) {
            return ['layers' => [], 'dependencies' => []];
        }

        $config = Yaml::parseFile($deptracPath);

        if (!is_array($config) || !isset($config['deptrac']) || !is_array($config['deptrac'])) {
            return ['layers' => [], 'dependencies' => []];
        }

        $deptracConfig = $config['deptrac'];

        $layers = [];
        if (isset($deptracConfig['layers']) && is_array($deptracConfig['layers'])) {
            $position = 0;
            foreach ($deptracConfig['layers'] as $layer) {
                if (is_array($layer) && isset($layer['name']) && is_string($layer['name'])) {
                    $layers[$layer['name']] = [
                        'name' => $layer['name'],
                        'position' => $position++,
                    ];
                }
            }
        }

        $dependencies = [];
        if (isset($deptracConfig['ruleset']) && is_array($deptracConfig['ruleset'])) {
            foreach ($deptracConfig['ruleset'] as $layer => $allowedDependencies) {
                if (is_string($layer)) {
                    $deps = [];
                    if (is_array($allowedDependencies)) {
                        $deps = array_values(array_filter($allowedDependencies, 'is_string'));
                    }
                    $dependencies[$layer] = $deps;
                }
            }
        }

        return [
            'layers' => $layers,
            'dependencies' => $dependencies,
        ];
    }
}
