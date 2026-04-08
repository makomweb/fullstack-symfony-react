<?php

declare(strict_types=1);

namespace App\Tests\Unit\Instrumentation\DataCollector;

use App\Instrumentation\DataCollector\PermissionVotesCollector;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionVotesCollectorTest extends TestCase
{
    #[Test]
    public function should_have_correct_name(): void
    {
        $collector = new PermissionVotesCollector();

        self::assertSame('app.permission_votes_collector', $collector->getName());
    }

    #[Test]
    public function should_return_empty_voter_calls_initially(): void
    {
        $collector = new PermissionVotesCollector();

        $calls = $collector->getVoterCalls();

        self::assertSame([], $calls);
    }

    #[Test]
    public function should_add_voter_call_with_string_subject(): void
    {
        $collector = new PermissionVotesCollector();

        $collector->addVoterCall('VIEW', 'string', true);

        $calls = $collector->getVoterCalls();
        self::assertCount(1, $calls);
        self::assertSame('VIEW', $calls[0]['attribute']);
        self::assertSame('string', $calls[0]['subject']);
        self::assertTrue($calls[0]['hasPermission']);
    }

    #[Test]
    public function should_add_voter_call_with_integer_subject(): void
    {
        $collector = new PermissionVotesCollector();

        $collector->addVoterCall('EDIT', 123, false);

        $calls = $collector->getVoterCalls();
        self::assertCount(1, $calls);
        self::assertSame('EDIT', $calls[0]['attribute']);
        self::assertSame('integer', $calls[0]['subject']);
        self::assertFalse($calls[0]['hasPermission']);
    }

    #[Test]
    public function should_add_voter_call_with_array_subject(): void
    {
        $collector = new PermissionVotesCollector();

        $collector->addVoterCall('DELETE', [], true);

        $calls = $collector->getVoterCalls();
        self::assertCount(1, $calls);
        self::assertSame('DELETE', $calls[0]['attribute']);
        self::assertSame('array', $calls[0]['subject']);
        self::assertTrue($calls[0]['hasPermission']);
    }

    #[Test]
    public function should_add_voter_call_with_object_subject(): void
    {
        $collector = new PermissionVotesCollector();
        $object = new \stdClass();

        $collector->addVoterCall('APPROVE', $object, true);

        $calls = $collector->getVoterCalls();
        self::assertCount(1, $calls);
        self::assertSame('APPROVE', $calls[0]['attribute']);
        self::assertSame('stdClass', $calls[0]['subject']);
        self::assertTrue($calls[0]['hasPermission']);
    }

    #[Test]
    public function should_add_multiple_voter_calls(): void
    {
        $collector = new PermissionVotesCollector();

        $collector->addVoterCall('VIEW', 'string', true);
        $collector->addVoterCall('EDIT', 'string', false);
        $collector->addVoterCall('DELETE', 'array', true);

        $calls = $collector->getVoterCalls();
        self::assertCount(3, $calls);
        self::assertSame('VIEW', $calls[0]['attribute']);
        self::assertSame('EDIT', $calls[1]['attribute']);
        self::assertSame('DELETE', $calls[2]['attribute']);
    }

    #[Test]
    public function should_collect_does_nothing(): void
    {
        $collector = new PermissionVotesCollector();
        $collector->addVoterCall('VIEW', 'string', true);

        $collector->collect(Request::create('/'), new Response());

        $calls = $collector->getVoterCalls();
        self::assertCount(1, $calls);
    }

    #[Test]
    public function should_collect_with_exception_does_nothing(): void
    {
        $collector = new PermissionVotesCollector();
        $collector->addVoterCall('VIEW', 'string', true);

        $exception = new \Exception('Test exception');
        $collector->collect(Request::create('/'), new Response(), $exception);

        $calls = $collector->getVoterCalls();
        self::assertCount(1, $calls);
    }

    #[Test]
    public function should_reset_voter_calls(): void
    {
        $collector = new PermissionVotesCollector();
        $collector->addVoterCall('VIEW', 'string', true);
        $collector->addVoterCall('EDIT', 'string', false);

        self::assertCount(2, $collector->getVoterCalls());

        $collector->reset();

        self::assertCount(0, $collector->getVoterCalls());
    }

    #[Test]
    public function should_get_attribute_stats_with_single_attribute(): void
    {
        $collector = new PermissionVotesCollector();
        $collector->addVoterCall('VIEW', 'string', true);
        $collector->addVoterCall('VIEW', 'integer', true);
        $collector->addVoterCall('VIEW', 'array', false);

        $stats = $collector->getAttributeStats();

        self::assertCount(1, $stats);
        self::assertSame(3, $stats['VIEW']);
    }

    #[Test]
    public function should_get_attribute_stats_with_multiple_attributes(): void
    {
        $collector = new PermissionVotesCollector();
        $collector->addVoterCall('VIEW', 'string', true);
        $collector->addVoterCall('VIEW', 'integer', true);
        $collector->addVoterCall('EDIT', 'string', false);
        $collector->addVoterCall('EDIT', 'integer', false);
        $collector->addVoterCall('EDIT', 'array', false);
        $collector->addVoterCall('DELETE', 'string', true);

        $stats = $collector->getAttributeStats();

        self::assertCount(3, $stats);
        self::assertSame(2, $stats['VIEW']);
        self::assertSame(3, $stats['EDIT']);
        self::assertSame(1, $stats['DELETE']);
    }

    #[Test]
    public function should_get_empty_attribute_stats_when_no_calls(): void
    {
        $collector = new PermissionVotesCollector();

        $stats = $collector->getAttributeStats();

        self::assertSame([], $stats);
    }

    #[Test]
    public function should_get_chart_labels_matching_attributes(): void
    {
        $collector = new PermissionVotesCollector();
        $collector->addVoterCall('VIEW', 'string', true);
        $collector->addVoterCall('EDIT', 'string', false);
        $collector->addVoterCall('DELETE', 'array', true);

        $labels = $collector->getChartLabels();

        self::assertCount(3, $labels);
        self::assertContains('VIEW', $labels);
        self::assertContains('EDIT', $labels);
        self::assertContains('DELETE', $labels);
    }

    #[Test]
    public function should_get_empty_chart_labels_when_no_calls(): void
    {
        $collector = new PermissionVotesCollector();

        $labels = $collector->getChartLabels();

        self::assertSame([], $labels);
    }

    #[Test]
    public function should_get_chart_data_matching_attribute_counts(): void
    {
        $collector = new PermissionVotesCollector();
        $collector->addVoterCall('VIEW', 'string', true);
        $collector->addVoterCall('VIEW', 'integer', true);
        $collector->addVoterCall('EDIT', 'string', false);
        $collector->addVoterCall('EDIT', 'integer', false);
        $collector->addVoterCall('EDIT', 'array', false);

        $data = $collector->getChartData();

        self::assertCount(2, $data);
        // Data values should match the counts (order may vary due to array_keys/array_values)
        sort($data);
        self::assertSame([2, 3], $data);
    }

    #[Test]
    public function should_get_empty_chart_data_when_no_calls(): void
    {
        $collector = new PermissionVotesCollector();

        $data = $collector->getChartData();

        self::assertSame([], $data);
    }

    #[Test]
    public function should_handle_null_subject(): void
    {
        $collector = new PermissionVotesCollector();

        $collector->addVoterCall('VIEW', null, true);

        $calls = $collector->getVoterCalls();
        self::assertCount(1, $calls);
        self::assertSame('NULL', $calls[0]['subject']);
    }

    #[Test]
    public function should_handle_boolean_subject(): void
    {
        $collector = new PermissionVotesCollector();

        $collector->addVoterCall('VIEW', true, false);

        $calls = $collector->getVoterCalls();
        self::assertCount(1, $calls);
        self::assertSame('boolean', $calls[0]['subject']);
    }

    #[Test]
    public function should_handle_float_subject(): void
    {
        $collector = new PermissionVotesCollector();

        $collector->addVoterCall('VIEW', 3.14, true);

        $calls = $collector->getVoterCalls();
        self::assertCount(1, $calls);
        self::assertSame('double', $calls[0]['subject']);
    }

    #[Test]
    public function should_handle_resource_subject(): void
    {
        $collector = new PermissionVotesCollector();
        $stream = fopen('php://memory', 'r');

        if (false === $stream) {
            self::markTestSkipped('Could not create stream resource');
        }

        try {
            $collector->addVoterCall('VIEW', $stream, true);

            $calls = $collector->getVoterCalls();
            self::assertCount(1, $calls);
            self::assertSame('resource', $calls[0]['subject']);
        } finally {
            fclose($stream);
        }
    }

    #[Test]
    public function should_track_permission_grants_and_denials(): void
    {
        $collector = new PermissionVotesCollector();

        $collector->addVoterCall('VIEW', 'string', true);
        $collector->addVoterCall('VIEW', 'integer', false);
        $collector->addVoterCall('VIEW', 'array', true);

        $calls = $collector->getVoterCalls();

        $granted = array_filter($calls, static fn ($call) => $call['hasPermission']);
        $denied = array_filter($calls, static fn ($call) => !$call['hasPermission']);

        self::assertCount(2, $granted);
        self::assertCount(1, $denied);
    }

    #[Test]
    public function should_preserve_insertion_order_in_voter_calls(): void
    {
        $collector = new PermissionVotesCollector();

        $attributes = ['FIRST', 'SECOND', 'THIRD', 'FOURTH', 'FIFTH'];
        foreach ($attributes as $attr) {
            $collector->addVoterCall($attr, 'string', true);
        }

        $calls = $collector->getVoterCalls();

        foreach ($attributes as $index => $attr) {
            self::assertSame($attr, $calls[$index]['attribute']);
        }
    }

    #[Test]
    public function should_count_same_attribute_with_different_subjects(): void
    {
        $collector = new PermissionVotesCollector();

        $collector->addVoterCall('VIEW', 'string', true);
        $collector->addVoterCall('VIEW', 'integer', true);
        $collector->addVoterCall('VIEW', new \stdClass(), false);
        $collector->addVoterCall('VIEW', [], true);

        $stats = $collector->getAttributeStats();

        self::assertSame(4, $stats['VIEW']);
    }

    #[Test]
    public function should_handle_same_attribute_and_subject_type_different_permissions(): void
    {
        $collector = new PermissionVotesCollector();

        $collector->addVoterCall('VIEW', 'string', true);
        $collector->addVoterCall('VIEW', 'string', false);
        $collector->addVoterCall('VIEW', 'string', true);

        $stats = $collector->getAttributeStats();

        self::assertSame(3, $stats['VIEW']);
    }

    #[Test]
    public function should_get_chart_labels_and_data_in_consistent_order(): void
    {
        $collector = new PermissionVotesCollector();
        $collector->addVoterCall('VIEW', 'string', true);
        $collector->addVoterCall('VIEW', 'integer', true);
        $collector->addVoterCall('EDIT', 'string', false);
        $collector->addVoterCall('EDIT', 'integer', false);
        $collector->addVoterCall('EDIT', 'array', false);
        $collector->addVoterCall('DELETE', 'string', true);

        $labels = $collector->getChartLabels();
        $data = $collector->getChartData();

        self::assertCount(count($labels), $data);
        self::assertCount(3, $labels);
        self::assertCount(3, $data);
    }

    #[Test]
    public function should_reset_clears_all_data(): void
    {
        $collector = new PermissionVotesCollector();
        $collector->addVoterCall('VIEW', 'string', true);
        $collector->addVoterCall('EDIT', 'integer', false);
        $collector->addVoterCall('DELETE', 'array', true);

        self::assertNotEmpty($collector->getVoterCalls());
        self::assertNotEmpty($collector->getAttributeStats());
        self::assertNotEmpty($collector->getChartLabels());
        self::assertNotEmpty($collector->getChartData());

        $collector->reset();

        self::assertEmpty($collector->getVoterCalls());
        self::assertEmpty($collector->getAttributeStats());
        self::assertEmpty($collector->getChartLabels());
        self::assertEmpty($collector->getChartData());
    }

    #[Test]
    public function should_add_calls_after_reset(): void
    {
        $collector = new PermissionVotesCollector();
        $collector->addVoterCall('VIEW', 'string', true);

        $collector->reset();
        $collector->addVoterCall('EDIT', 'integer', false);

        $calls = $collector->getVoterCalls();

        self::assertCount(1, $calls);
        self::assertSame('EDIT', $calls[0]['attribute']);
    }

    #[Test]
    public function should_handle_unicode_attribute_names(): void
    {
        $collector = new PermissionVotesCollector();

        $collector->addVoterCall('ANZEIGEN', 'string', true);
        $collector->addVoterCall('BEARBEITEN', 'string', false);
        $collector->addVoterCall('LÖSCHEN', 'string', true);

        $stats = $collector->getAttributeStats();

        self::assertCount(3, $stats);
        self::assertSame(1, $stats['ANZEIGEN']);
        self::assertSame(1, $stats['BEARBEITEN']);
        self::assertSame(1, $stats['LÖSCHEN']);
    }

    #[Test]
    public function should_handle_case_sensitive_attribute_names(): void
    {
        $collector = new PermissionVotesCollector();

        $collector->addVoterCall('view', 'string', true);
        $collector->addVoterCall('VIEW', 'string', false);
        $collector->addVoterCall('View', 'string', true);

        $stats = $collector->getAttributeStats();

        self::assertCount(3, $stats);
        self::assertSame(1, $stats['view']);
        self::assertSame(1, $stats['VIEW']);
        self::assertSame(1, $stats['View']);
    }
}
