<?php

/**
 * InsuranceCompanyService Search Tests
 *
 * Verifies InsuranceCompanyService::search() filtering with typed search
 * fields, backing the query parameter support on GET /api/insurance_company.
 *
 * Runs in the services test suite (requires a database connection).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\InsuranceCompanyService;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InsuranceCompanyServiceSearchTest extends TestCase
{
    private const NAME_PREFIX = 'test-fixture-Search ';

    private InsuranceCompanyService $service;

    /** @var list<int|string> */
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->service = new InsuranceCompanyService();
        $this->insertAndTrack(self::NAME_PREFIX . 'Alpha', '77771');
        $this->insertAndTrack(self::NAME_PREFIX . 'Beta', '77772');
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            QueryUtils::fetchRecordsNoLog("DELETE FROM `addresses` WHERE `foreign_id` = ?", [$id]);
            QueryUtils::fetchRecordsNoLog("DELETE FROM `phone_numbers` WHERE `foreign_id` = ?", [$id]);
            QueryUtils::fetchRecordsNoLog("DELETE FROM `insurance_companies` WHERE `id` = ?", [$id]);
        }
    }

    private function insertAndTrack(string $name, string $cmsId): void
    {
        $id = $this->service->insert([
            'name' => $name,
            'attn' => 'Claims Department',
            'cms_id' => $cmsId,
            'ins_type_code' => '1',
            'x12_receiver_id' => '',
            'x12_default_partner_id' => '',
            'alt_cms_id' => '',
            'line1' => '100 Insurance Blvd',
            'line2' => '',
            'city' => 'Rutland',
            'state' => 'VT',
            'zip' => '05701',
            'country' => 'USA',
        ]);
        $this->createdIds[] = $id;
    }

    #[Test]
    public function testSearchByNameContains(): void
    {
        $result = $this->service->search([
            'name' => new StringSearchField('name', [self::NAME_PREFIX], SearchModifier::CONTAINS),
        ]);
        $this->assertTrue($result->isValid());
        /** @var list<array{name: string}> $data */
        $data = $result->getData();
        $this->assertCount(2, $data);
        foreach ($data as $record) {
            $this->assertStringContainsString(self::NAME_PREFIX, $record['name']);
        }
    }

    #[Test]
    public function testSearchByCmsIdExact(): void
    {
        $result = $this->service->search([
            'cms_id' => new StringSearchField('cms_id', ['77771'], SearchModifier::EXACT),
        ]);
        $this->assertTrue($result->isValid());
        /** @var list<array{name: string}> $data */
        $data = $result->getData();
        $this->assertCount(1, $data);
        $this->assertEquals(self::NAME_PREFIX . 'Alpha', $data[0]['name']);
    }

    #[Test]
    public function testSearchCombinedFieldsAndCondition(): void
    {
        $result = $this->service->search([
            'name' => new StringSearchField('name', [self::NAME_PREFIX], SearchModifier::CONTAINS),
            'cms_id' => new StringSearchField('cms_id', ['77772'], SearchModifier::EXACT),
        ]);
        $this->assertTrue($result->isValid());
        /** @var list<array{name: string}> $data */
        $data = $result->getData();
        $this->assertCount(1, $data);
        $this->assertEquals(self::NAME_PREFIX . 'Beta', $data[0]['name']);
    }

    #[Test]
    public function testSearchNoMatchesReturnsValidEmptyResult(): void
    {
        $result = $this->service->search([
            'name' => new StringSearchField('name', ['test-fixture-NoSuchCompany'], SearchModifier::EXACT),
        ]);
        $this->assertTrue($result->isValid());
        /** @var list<mixed> $data */
        $data = $result->getData();
        $this->assertCount(0, $data);
    }
}
