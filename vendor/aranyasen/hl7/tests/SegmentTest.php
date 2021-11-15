<?php

declare(strict_types=1);

namespace Aranyasen\HL7\Tests;

use Aranyasen\HL7\Segment;

class SegmentTest extends TestCase
{
    /** @test */
    public function field_at_a_given_nonzero_index_can_be_set(): void
    {
        $seg = new Segment('XXX');
        $seg->setField(1, 'YYY');
        self::assertSame('YYY', $seg->getField(1), 'Field 1 is YYY');
    }

    /** @test */
    public function field_at_index_0_can_not_be_changed(): void
    {
        $seg = new Segment('XXX');
        $seg->setField(0, 'YYY');
        self::assertNotSame('YYY', $seg->getField(0), 'Field 0 has not been changed');
        self::assertSame('XXX', $seg->getField(0), 'Field 0 is still the same');
        self::assertSame('XXX', $seg->getName(), 'Segment name is still the same');
    }

    /** @test */
    public function a_segment_can_be_constructed_from_an_array(): void
    {
        $seg = new Segment('XXX', ['a', 'b', 'c', ['p', 'q', 'r'], 'd']);
        self::assertSame('c', $seg->getField(3), 'Constructor with array');
        self::assertSame('r', $seg->getField(4)[2], 'Constructor with array for composed fields');
    }

    /** @test */
    public function field_can_be_cleared(): void
    {
        $segment = new Segment('XXX', ['a']);
        self::assertSame('a', $segment->getField(1));
        $segment->clearField(1);
        self::assertNull($segment->getField(1), 'Field 1 should be NULL');
    }

    /** @test */
    public function field_can_be_set_using_array(): void
    {
        $seg = new Segment('XXX');
        $seg->setField(3, ['1', '2', '3']);
        self::assertIsArray($seg->getField(3), 'Composed field 1^2^3');
        self::assertCount(3, $seg->getField(3), 'Getting composed fields as array');
        self::assertSame('2', $seg->getField(3)[1], 'Getting single value from composed field');
    }

    /** @test */
    public function fields_from_a_given_position_to_end_can_be_retrieved_in_an_array(): void
    {
        $seg = new Segment('XXX');
        $seg->setField(8, 'aaa');
        self::assertCount(7, $seg->getFields(2), 'Getting all fields from 2nd index');
    }

    /** @test */
    public function a_chunk_of_fields_can_be_retrieved_from_a_segment(): void
    {
        $seg = new Segment('XXX');
        $seg->setField(8, 'aaa');
        self::assertCount(3, $seg->getFields(2, 4), 'Getting fields from 2 till 4');
    }

    /** @test */
    public function setting_field_beyond_last_index_creates_empty_fields_in_between(): void
    {
        $seg = new Segment('XXX');
        $seg->setField(8, 'aaa');
        self::assertCount(9, $seg->getFields(), 'Number of fields in segment');
    }

    /** @test */
    public function total_size_of_a_segment_can_be_obtained(): void
    {
        $seg = new Segment('XXX');
        $seg->setField(8, ['']);
        self::assertSame(8, $seg->size(), 'Size operator');
        $seg->setField(12, 'x');
        self::assertSame(12, $seg->size(), 'Size operator');
    }

    /** @test */
    public function a_field_can_have_0_as_a_value(): void
    {
        $segment = new Segment('XXX');

        $segment->setField(1, 0);
        self::assertSame(0, $segment->getField(1));

        $segment->setField(1, '0');
        self::assertSame('0', $segment->getField(1));
    }
}
