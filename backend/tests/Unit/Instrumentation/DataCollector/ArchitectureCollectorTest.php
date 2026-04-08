<?php

declare(strict_types=1);

namespace App\Tests\Unit\Instrumentation\DataCollector;

use App\Instrumentation\DataCollector\ArchitectureCollector;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArchitectureCollectorTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/architecture_collector_test_'.uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ('.' !== $file && '..' !== $file) {
                    $path = $dir.DIRECTORY_SEPARATOR.$file;
                    is_dir($path) ? $this->removeDirectory($path) : unlink($path);
                }
            }
            rmdir($dir);
        }
    }

    #[Test]
    public function should_have_correct_name(): void
    {
        $collector = new ArchitectureCollector($this->tempDir);

        self::assertSame('app.architecture_collector', $collector->getName());
    }

    #[Test]
    public function should_return_empty_details_when_deptrac_file_does_not_exist(): void
    {
        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertSame(['layers' => [], 'dependencies' => []], $details);
    }

    #[Test]
    public function should_return_empty_details_when_deptrac_yaml_is_invalid(): void
    {
        file_put_contents($this->tempDir.'/deptrac.yaml', 'invalid: {yaml: [content');

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertSame(['layers' => [], 'dependencies' => []], $details);
    }

    #[Test]
    public function should_return_empty_details_when_deptrac_section_is_missing(): void
    {
        file_put_contents($this->tempDir.'/deptrac.yaml', 'other_key: value');

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertSame(['layers' => [], 'dependencies' => []], $details);
    }

    #[Test]
    public function should_return_empty_details_when_deptrac_section_is_not_array(): void
    {
        file_put_contents($this->tempDir.'/deptrac.yaml', 'deptrac: "not an array"');

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertSame(['layers' => [], 'dependencies' => []], $details);
    }

    #[Test]
    public function should_parse_layers_from_deptrac_config(): void
    {
        $yaml = <<<'YAML'
deptrac:
  layers:
    - name: Core
    - name: Generic
    - name: Supporting
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertArrayHasKey('layers', $details);
        self::assertCount(3, $details['layers']);
        self::assertArrayHasKey('Core', $details['layers']);
        self::assertArrayHasKey('Generic', $details['layers']);
        self::assertArrayHasKey('Supporting', $details['layers']);

        self::assertSame(['name' => 'Core', 'position' => 0], $details['layers']['Core']);
        self::assertSame(['name' => 'Generic', 'position' => 1], $details['layers']['Generic']);
        self::assertSame(['name' => 'Supporting', 'position' => 2], $details['layers']['Supporting']);
    }

    #[Test]
    public function should_skip_invalid_layers(): void
    {
        $yaml = <<<'YAML'
deptrac:
  layers:
    - name: Core
    - invalid_layer
    - name: Generic
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertCount(2, $details['layers']);
        self::assertArrayHasKey('Core', $details['layers']);
        self::assertArrayHasKey('Generic', $details['layers']);
    }

    #[Test]
    public function should_parse_dependencies_from_ruleset(): void
    {
        $yaml = <<<'YAML'
deptrac:
  layers:
    - name: Core
    - name: Generic
    - name: Supporting
  ruleset:
    Core: ~
    Generic: ~
    Supporting:
      - Core
      - Generic
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertArrayHasKey('dependencies', $details);
        self::assertSame([], $details['dependencies']['Core']);
        self::assertSame([], $details['dependencies']['Generic']);
        self::assertSame(['Core', 'Generic'], $details['dependencies']['Supporting']);
    }

    #[Test]
    public function should_handle_null_dependencies(): void
    {
        $yaml = <<<'YAML'
deptrac:
  layers:
    - name: Core
  ruleset:
    Core: ~
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertSame([], $details['dependencies']['Core']);
    }

    #[Test]
    public function should_filter_non_string_dependencies(): void
    {
        $yaml = <<<'YAML'
deptrac:
  layers:
    - name: Core
    - name: Generic
    - name: Supporting
  ruleset:
    Supporting:
      - Core
      - 123
      - Generic
      - true
      - ~
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertSame(['Core', 'Generic'], $details['dependencies']['Supporting']);
    }

    #[Test]
    public function should_handle_missing_layers_section(): void
    {
        $yaml = <<<'YAML'
deptrac:
  ruleset:
    Core: ~
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertSame([], $details['layers']);
        self::assertSame(['Core' => []], $details['dependencies']);
    }

    #[Test]
    public function should_handle_missing_ruleset_section(): void
    {
        $yaml = <<<'YAML'
deptrac:
  layers:
    - name: Core
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertSame(['Core' => ['name' => 'Core', 'position' => 0]], $details['layers']);
        self::assertSame([], $details['dependencies']);
    }

    #[Test]
    public function should_handle_non_array_layer_entries(): void
    {
        $yaml = <<<'YAML'
deptrac:
  layers:
    - name: Core
    - "string layer"
    - name: Generic
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertCount(2, $details['layers']);
        self::assertArrayHasKey('Core', $details['layers']);
        self::assertArrayHasKey('Generic', $details['layers']);
    }

    #[Test]
    public function should_handle_layer_with_missing_name(): void
    {
        $yaml = <<<'YAML'
deptrac:
  layers:
    - name: Core
    - collectors: []
    - name: Generic
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertCount(2, $details['layers']);
        self::assertArrayHasKey('Core', $details['layers']);
        self::assertArrayHasKey('Generic', $details['layers']);
    }

    #[Test]
    public function should_handle_non_string_layer_names(): void
    {
        $yaml = <<<'YAML'
deptrac:
  layers:
    - name: Core
    - name: 123
    - name: Generic
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertCount(2, $details['layers']);
        self::assertArrayHasKey('Core', $details['layers']);
        self::assertArrayHasKey('Generic', $details['layers']);
    }

    #[Test]
    public function should_handle_complex_deptrac_config(): void
    {
        $yaml = <<<'YAML'
deptrac:
  paths:
    - ./src
    - ./tests
  layers:
    - name: Core
      collectors:
        - type: classLike
          value: .*App\\Game\\.*
    - name: Generic
      collectors:
        - type: classLike
          value: .*Symfony\\.*
    - name: Supporting
      collectors:
        - type: classLike
          value: .*App\\Controller\\.*
    - name: Tests
      collectors:
        - type: classLike
          value: .*App\\Tests\\.*
  ruleset:
    Core: ~
    Generic: ~
    Supporting:
      - Core
      - Generic
    Tests:
      - Core
      - Generic
      - Supporting
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertCount(4, $details['layers']);
        self::assertArrayHasKey('Core', $details['layers']);
        self::assertArrayHasKey('Generic', $details['layers']);
        self::assertArrayHasKey('Supporting', $details['layers']);
        self::assertArrayHasKey('Tests', $details['layers']);

        self::assertSame(['Core', 'Generic'], $details['dependencies']['Supporting']);
        self::assertSame(['Core', 'Generic', 'Supporting'], $details['dependencies']['Tests']);
    }

    #[Test]
    public function collect_should_populate_data(): void
    {
        $yaml = <<<'YAML'
deptrac:
  layers:
    - name: Core
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $request = Request::create('/');
        $response = new Response();

        $collector->collect($request, $response);

        $details = $collector->getDetails();
        self::assertNotEmpty($details['layers']);
    }

    #[Test]
    public function collect_should_handle_exception_parameter(): void
    {
        $yaml = <<<'YAML'
deptrac:
  layers:
    - name: Core
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $request = Request::create('/');
        $response = new Response();
        $exception = new \Exception('Test exception');

        $collector->collect($request, $response, $exception);

        $details = $collector->getDetails();
        self::assertNotEmpty($details['layers']);
    }

    #[Test]
    public function should_reset_positions_correctly_when_layer_is_skipped(): void
    {
        $yaml = <<<'YAML'
deptrac:
  layers:
    - name: Layer1
    - invalid_entry
    - name: Layer2
    - another_invalid
    - name: Layer3
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertSame(0, $details['layers']['Layer1']['position']);
        self::assertSame(1, $details['layers']['Layer2']['position']);
        self::assertSame(2, $details['layers']['Layer3']['position']);
    }

    #[Test]
    public function should_handle_ruleset_with_non_string_keys(): void
    {
        $yaml = <<<'YAML'
deptrac:
  layers:
    - name: Core
    - name: Generic
  ruleset:
    Core: ~
    123:
      - Generic
YAML;
        file_put_contents($this->tempDir.'/deptrac.yaml', $yaml);

        $collector = new ArchitectureCollector($this->tempDir);
        $collector->collect(Request::create('/'), new Response());

        $details = $collector->getDetails();

        self::assertArrayHasKey('Core', $details['dependencies']);
        self::assertCount(1, $details['dependencies']);
    }
}
