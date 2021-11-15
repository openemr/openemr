<?php

declare(strict_types=1);

namespace Aranyasen\HL7\Tests\Segments;

use Aranyasen\HL7\Segments\MSH;
use Aranyasen\HL7\Tests\TestCase;

class MSHTest extends TestCase
{
    /** @test */
    public function MSH_formed_without_any_arguments_should_have_mandatory_fields_set_automatically(): void
    {
        $msh = new MSH();
        self::assertSame('|', $msh->getField(1));
        self::assertSame('^~\\&', $msh->getField(2));
        self::assertSame('2.3', $msh->getVersionId());
        self::assertNotEmpty($msh->getDateTimeOfMessage());
        self::assertNotEmpty($msh->getMessageControlId());
    }

    /** @test
     * @throws \Exception
     */
    public function an_array_of_fields_can_be_passed_to_constructor_to_construct_MSH(): void
    {
        $msh = new MSH(['MSH', '^~\&', 'HL7 Corp', 'HL7 HQ', 'VISION', 'MISYS', '200404061744', '', ['DFT', 'P03'], 'TC-22222', 'T', '2.3']);
        self::assertSame(
            ['MSH', '', '^~\&', 'HL7 Corp', 'HL7 HQ', 'VISION', 'MISYS', '200404061744', '', ['DFT', 'P03'], 'TC-22222', 'T', '2.3'],
            $msh->getFields()
        );
    }

    /** @test */
    public function field_separator_can_be_set(): void
    {
        $msh = new MSH();
        $msh->setField(1, '*');
        self::assertSame('*', $msh->getField(1), 'MSH Field sep field (MSH(1))');
    }

    /** @test */
    public function more_than_one_character_as_field_separator_is_not_accepted(): void
    {
        $msh = new MSH();
        $msh->setField(1, 'xx');
        self::assertSame('|', $msh->getField(1), 'MSH Field sep field (MSH(1))');
    }

    /** @test */
    public function index_2_in_MSH_accepts_only_4_character_strings(): void
    {
        $msh = new MSH();
        $msh->setField(2, 'xxxx');
        self::assertSame('xxxx', $msh->getField(2), 'Special fields not changed');

        $msh->setField(2, 'yyyyy');
        self::assertSame('xxxx', $msh->getField(2), 'Special fields not changed');
    }
}
