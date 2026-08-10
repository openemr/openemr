<?php

/**
 * DB-backed tests for the fetchFormOwner() lookup in
 * `OpenEMR\Common\Forms\EncounterFormAccess`.
 *
 * The pure comparison in `isFormOwnedBySession()` is covered in isolation at
 * `tests/Tests/Isolated/Common/Forms/EncounterFormAccessTest.php`. This suite
 * exercises the DB-touching primitive so a schema drift or query regression
 * surfaces in CI.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Common\Forms;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Forms\EncounterFormAccess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EncounterFormAccessTest extends TestCase
{
    private const FORMDIR = 'encounter_form_access_test';

    /** @var list<int> */
    private array $insertedFormIds = [];

    protected function setUp(): void
    {
        // Ensure no leftover rows from a prior failed run.
        QueryUtils::sqlStatementThrowException('DELETE FROM forms WHERE formdir = ?', [self::FORMDIR]);
    }

    protected function tearDown(): void
    {
        foreach ($this->insertedFormIds as $id) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM forms WHERE form_id = ? AND formdir = ?',
                [$id, self::FORMDIR],
            );
        }
        $this->insertedFormIds = [];
    }

    #[Test]
    public function fetchFormOwnerReturnsPidAndEncounterForExistingRow(): void
    {
        $formId = $this->insertFormsRow(pid: 42, encounter: 100, deleted: 0);

        $owner = EncounterFormAccess::fetchFormOwner($formId, self::FORMDIR);

        $this->assertNotNull($owner);
        $this->assertSame(42, $owner['pid']);
        $this->assertSame(100, $owner['encounter']);
    }

    #[Test]
    public function fetchFormOwnerReturnsNullForAbsentRow(): void
    {
        // No row inserted for form_id 999_999.
        $this->assertNull(EncounterFormAccess::fetchFormOwner(999999, self::FORMDIR));
    }

    #[Test]
    public function fetchFormOwnerReturnsNullForDeletedRow(): void
    {
        $formId = $this->insertFormsRow(pid: 42, encounter: 100, deleted: 1);

        $this->assertNull(EncounterFormAccess::fetchFormOwner($formId, self::FORMDIR));
    }

    #[Test]
    public function fetchFormOwnerScopesByFormdir(): void
    {
        // Same form_id under a different formdir should not match.
        $formId = $this->insertFormsRow(pid: 42, encounter: 100, deleted: 0);

        $this->assertNull(EncounterFormAccess::fetchFormOwner($formId, 'unrelated_formdir'));
    }

    /**
     * Insert a `forms` row for testing. Returns the generated form_id.
     */
    private function insertFormsRow(int $pid, int $encounter, int $deleted): int
    {
        $formId = QueryUtils::sqlInsert(
            'INSERT INTO forms (date, encounter, form_name, pid, user, groupname, authorized, formdir, deleted) '
            . 'VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)',
            [$encounter, 'EncounterFormAccessTest form', $pid, 'test', 'Default', 1, self::FORMDIR, $deleted],
        );
        // `forms.form_id` is a distinct column from `forms.id`; the schema stores the
        // form-specific-table's PK in `form_id`. For test purposes we set form_id to
        // match the just-inserted row id — mirrors what `addForm()` does in prod.
        QueryUtils::sqlStatementThrowException(
            'UPDATE forms SET form_id = ? WHERE id = ?',
            [$formId, $formId],
        );
        $this->insertedFormIds[] = $formId;
        return $formId;
    }
}
