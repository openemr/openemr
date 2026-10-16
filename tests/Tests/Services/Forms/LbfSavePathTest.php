<?php

/**
 * A layout form save writes the visit, the patient, and its own fields.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Forms;

require_once __DIR__ . '/../../../../library/options.inc.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LbfSavePathTest extends TestCase
{
    private const PID = 99055077;

    private const ENCOUNTER = 99055077;

    private const FORM = 'LBFZZSave';

    /** @var array<mixed> */
    private array $savedPost = [];

    private bool $hadPid = false;

    private mixed $savedPid = null;

    /**
     * Save the request and the patient id, then build this run's rows.
     */
    protected function setUp(): void
    {
        $this->savedPost = $_POST;
        $this->hadPid = array_key_exists('pid', $GLOBALS);
        $this->savedPid = $GLOBALS['pid'] ?? null;
        $this->removeFixtures();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, fname, lname, DOB, sex, pubpid, city) VALUES (?, 'ZZ', 'Layout', '1980-01-01', 'Male', ?, 'before')",
            [self::PID, 'ZZ' . self::PID]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO form_encounter (pid, encounter, date, reason, provider_id, facility_id, pc_catid) VALUES (?, ?, NOW(), 'before', 1, 3, 5)",
            [self::PID, self::ENCOUNTER]
        );
        $this->layoutField('reason', 'V', 10, 2, '');
        $this->layoutField('city', 'D', 20, 2, '');
        $this->layoutField('Note', '', 30, 2, '');
        $this->layoutField('Flags', '', 40, 21, 'yesno');
        $this->layoutField('Lines', '', 50, 22, 'yesno');
    }

    /**
     * Restore the request and the patient id, then delete the rows this run inserted.
     */
    protected function tearDown(): void
    {
        $_POST = $this->savedPost;
        if ($this->hadPid) {
            $GLOBALS['pid'] = $this->savedPid;
        } else {
            unset($GLOBALS['pid']);
        }
        $this->removeFixtures();
    }

    /**
     * Posted text is trimmed, flags are joined, and pipes are removed.
     */
    #[Test]
    public function testPostedValuesAreTrimmedJoinedAndClearedOfPipes(): void
    {
        $_POST = [
            'form_reason' => '  ZZ reason  ',
            'form_Flags' => ['YES' => 'on', 'NO' => 'on'],
            'form_Lines' => ['1' => 'a|b', '2' => 'c'],
        ];

        $this->assertSame('ZZ reason', \get_layout_form_value($this->field('reason')));
        $this->assertSame('YES|NO', \get_layout_form_value($this->field('Flags', 21, 'yesno')));
        $this->assertSame('1:a b|2:c', \get_layout_form_value($this->field('Lines', 22, 'yesno')));
        $this->assertSame('', \get_layout_form_value($this->field('Note')));
    }

    /**
     * The save stores the visit, the patient, and the form field.
     */
    #[Test]
    public function testThePageSavesVisitPatientAndFormFields(): void
    {
        $body = $this->postForm([
            'form_reason' => 'ZZ visit',
            'form_city' => 'ZZ city',
            'form_Note' => 'ZZ note',
            'bn_save' => 'Save',
        ]);

        $this->assertStringNotContainsString('query failed', strtolower($body));
        $this->assertSame('ZZ visit', $this->encounterReason());
        $this->assertSame('ZZ city', $this->patientCity());
        $this->assertSame('ZZ note', $this->noteValue());
        $this->assertSame(self::PID, $this->patientId());
    }

    /**
     * A blank form field removes the stored value.
     */
    #[Test]
    public function testAnEmptyFormFieldDeletesTheStoredValue(): void
    {
        $this->postForm([
            'form_reason' => 'ZZ visit',
            'form_city' => 'ZZ city',
            'form_Note' => 'ZZ note',
            'bn_save' => 'Save',
        ]);
        $formId = $this->formId();
        $this->postForm([
            'form_reason' => 'ZZ visit 2',
            'form_city' => 'ZZ city 2',
            'form_Note' => '',
            'bn_save' => 'Save',
        ], $formId);

        $this->assertSame('ZZ visit 2', $this->encounterReason());
        $this->assertSame('ZZ city 2', $this->patientCity());
        $this->assertNull($this->noteValue());
    }

    /**
     * A refused column stops the save before a form row is created.
     */
    #[Test]
    public function testARefusedColumnStopsTheSaveAndLeavesTheRowKey(): void
    {
        $this->layoutField('pid', 'D', 1, 2, '');
        $body = $this->postForm([
            'form_pid' => '1',
            'form_reason' => 'ZZ should not land',
            'bn_save' => 'Save',
        ]);

        $this->assertStringContainsString('An error has occurred.', $body);
        $this->assertSame('before', $this->encounterReason());
        $this->assertSame(self::PID, $this->patientId());
        $row = QueryUtils::querySingleRow(
            'SELECT COUNT(*) AS c FROM forms WHERE pid = ? AND formdir = ? AND deleted = 0',
            [self::PID, self::FORM]
        );
        $this->assertIsArray($row);
        $this->assertSame(0, $this->whole($row['c'] ?? null));
    }


    /**
     * A history field is stored and read back.
     */
    #[Test]
    public function testAHistoryFieldIsStoredAndReadBack(): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO history_data (pid, tobacco) VALUES (?, ?)',
            [self::PID, 'old']
        );
        $this->layoutField('tobacco', 'H', 60, 2, '');
        $this->postForm([
            'form_reason' => 'ZZ visit',
            'form_tobacco' => 'ZZ tobacco',
            'bn_save' => 'Save',
        ]);
        $GLOBALS['pid'] = self::PID;

        $row = QueryUtils::querySingleRow(
            'SELECT tobacco FROM history_data WHERE pid = ? ORDER BY id DESC LIMIT 1',
            [self::PID]
        );
        $this->assertIsArray($row);
        $this->assertSame('ZZ tobacco', $row['tobacco']);
        $this->assertSame('ZZ tobacco', \lbf_current_value($this->storedField('tobacco', 'H'), 0, self::ENCOUNTER));
    }

    /**
     * An employer field is stored, and columns this save omits stay put.
     *
     * The seeded row is a day old so it cannot tie the saved row's timestamp.
     * A blank occupation still clears that column.
     */
    #[Test]
    public function testAnEmployerFieldIsStored(): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO employer_data (pid, occupation, industry, date) VALUES (?, ?, ?, DATE_SUB(NOW(), INTERVAL 1 DAY))',
            [self::PID, 'old', 'ZZ mill']
        );
        $this->layoutField('em_occupation', 'D', 70, 2, '');
        $this->postForm([
            'form_em_occupation' => 'ZZ nurse',
            'bn_save' => 'Save',
        ]);

        $row = QueryUtils::querySingleRow(
            'SELECT occupation, industry FROM employer_data WHERE pid = ? ORDER BY date DESC, id DESC LIMIT 1',
            [self::PID]
        );
        $this->assertIsArray($row);
        $this->assertSame('ZZ nurse', $row['occupation']);
        $this->assertSame('ZZ mill', $row['industry']);

        $this->postForm([
            'form_em_occupation' => '',
            'bn_save' => 'Save',
        ]);
        $cleared = QueryUtils::querySingleRow(
            'SELECT occupation, industry FROM employer_data WHERE pid = ? ORDER BY date DESC, id DESC LIMIT 1',
            [self::PID]
        );
        $this->assertIsArray($cleared);
        $this->assertSame('', $cleared['occupation']);
        $this->assertSame('ZZ mill', $cleared['industry']);
    }

    /**
     * A shared layout value is stored and read back.
     */
    #[Test]
    public function testASharedValueIsStoredAndReadBack(): void
    {
        $this->layoutField('ShareNote', 'E', 80, 2, '');
        $this->postForm([
            'form_ShareNote' => 'ZZ shared',
            'bn_save' => 'Save',
        ]);
        $GLOBALS['pid'] = self::PID;

        $row = QueryUtils::querySingleRow(
            'SELECT field_value FROM shared_attributes WHERE pid = ? AND encounter = ? AND field_id = ?',
            [self::PID, self::ENCOUNTER, 'ShareNote']
        );
        $this->assertIsArray($row);
        $this->assertSame('ZZ shared', $row['field_value']);
        $this->assertSame('ZZ shared', \lbf_current_value($this->storedField('ShareNote', 'E'), 0, self::ENCOUNTER));
    }

    /**
     * Visit and patient values are read back from their tables.
     */
    #[Test]
    public function testAVisitAndPatientValueAreReadBackFromTheirTables(): void
    {
        $this->postForm([
            'form_reason' => 'ZZ visit',
            'form_city' => 'ZZ city',
            'bn_save' => 'Save',
        ]);
        $GLOBALS['pid'] = self::PID;

        $this->assertSame('ZZ visit', \lbf_current_value($this->storedField('reason', 'V'), 0, self::ENCOUNTER));
        $this->assertSame('ZZ city', \lbf_current_value($this->storedField('city', 'D'), 0, self::ENCOUNTER));
        $this->assertSame('*?*', \lbf_current_value($this->storedField('not_a_column', 'D'), 0, self::ENCOUNTER));
    }

    /**
     * A second save updates the same form.
     */
    #[Test]
    public function testASecondSaveDoesNotOpenAnotherForm(): void
    {
        $this->postForm([
            'form_reason' => 'ZZ visit',
            'form_Note' => 'ZZ note',
            'bn_save' => 'Save',
        ]);
        $this->postForm([
            'form_reason' => 'ZZ visit 2',
            'form_Note' => 'ZZ note 2',
            'bn_save' => 'Save',
        ], $this->formId());

        $row = QueryUtils::querySingleRow(
            'SELECT COUNT(*) AS c FROM forms WHERE pid = ? AND formdir = ? AND deleted = 0',
            [self::PID, self::FORM]
        );
        $this->assertIsArray($row);
        $this->assertSame(1, $this->whole($row['c'] ?? null));
    }

    /**
     * A bare checkbox and a date are stored in their field shapes.
     */
    #[Test]
    public function testABareCheckboxAndADateAreParsed(): void
    {
        $_POST = [
            'form_Plain' => 'on',
            'form_When' => '2024-06-03',
        ];

        $this->assertSame('Yes', \get_layout_form_value($this->field('Plain', 21, '')));
        $this->assertSame(
            \DateToYYYYMMDD('2024-06-03'),
            \get_layout_form_value($this->field('When', 4, ''))
        );
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function postForm(array $fields, int $formId = 0): string
    {
        $cookies = new CookieJar();
        $base = $this->appBase();
        $this->request($cookies, $base . '/interface/login/login.php?site=default');
        $this->request($cookies, $base . '/interface/main/main_screen.php?auth=login&site=default', [
            'authUser' => 'admin',
            'clearPass' => 'pass',
            'new_login_session_management' => '1',
            'languageChoice' => '1',
        ]);
        $this->request(
            $cookies,
            $base . '/interface/patient_file/encounter/encounter_top.php?set_pid=' . self::PID . '&set_encounter=' . self::ENCOUNTER
        );

        return $this->request(
            $cookies,
            $base . '/interface/forms/LBF/new.php?formname=' . self::FORM . '&id=' . $formId,
            $fields
        );
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function request(CookieJar $cookies, string $url, array $fields = []): string
    {
        $client = new Client([
            'cookies' => $cookies,
            'http_errors' => false,
            'allow_redirects' => true,
            'verify' => false,
        ]);
        $response = $fields === []
            ? $client->get($url)
            : $client->post($url, ['form_params' => $fields]);

        return (string) $response->getBody();
    }


    /**
     * Web root for this run.
     *
     * CI nginx is a different container from PHP, named by
     * OPENEMR_BASE_URL_API. Apache leaves that unset and serves
     * https://localhost, which is the suite default.
     */
    private function appBase(): string
    {
        $base = getenv('OPENEMR_BASE_URL_API');
        if (!is_string($base) || $base === '') {
            $base = 'https://localhost';
        }

        return rtrim($base, '/');
    }

    /**
     * Insert one layout option for this form.
     */
    private function layoutField(string $fieldId, string $source, int $seq, int $dataType, string $listId): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO layout_options
                (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description, fld_rows, source)
             VALUES (?, ?, ?, ?, ?, ?, 1, 0, 0, ?, 1, 1, ?, ?, ?, 0, ?)',
            [self::FORM, $fieldId, '1', $fieldId, $seq, $dataType, $listId, '', '', '', $source]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function field(string $fieldId, int $dataType = 2, string $listId = ''): array
    {
        return [
            'field_id' => $fieldId,
            'data_type' => $dataType,
            'list_id' => $listId,
            'max_length' => 0,
            'edit_options' => '',
        ];
    }

    /**
     * Read the encounter reason stored for this patient.
     */
    private function encounterReason(): string
    {
        $row = QueryUtils::querySingleRow(
            'SELECT reason FROM form_encounter WHERE pid = ? AND encounter = ?',
            [self::PID, self::ENCOUNTER]
        );
        $this->assertIsArray($row);
        $value = $row['reason'] ?? null;
        $this->assertIsString($value);

        return $value;
    }

    /**
     * Read the patient city stored for this run.
     */
    private function patientCity(): string
    {
        $row = QueryUtils::querySingleRow('SELECT city FROM patient_data WHERE pid = ?', [self::PID]);
        $this->assertIsArray($row);
        $value = $row['city'] ?? null;
        $this->assertIsString($value);

        return $value;
    }

    /**
     * Read the patient id this run created.
     */
    private function patientId(): int
    {
        $row = QueryUtils::querySingleRow('SELECT pid FROM patient_data WHERE fname = ? AND lname = ?', ['ZZ', 'Layout']);
        $this->assertIsArray($row);
        return $this->whole($row['pid'] ?? null);
    }

    /**
     * Read the saved note, or null when it is absent.
     */
    private function noteValue(): ?string
    {
        $row = QueryUtils::querySingleRow(
            'SELECT d.field_value FROM lbf_data d JOIN forms f ON f.form_id = d.form_id WHERE f.pid = ? AND f.formdir = ? AND d.field_id = ? AND f.deleted = 0',
            [self::PID, self::FORM, 'Note']
        );
        if (!is_array($row)) {
            return null;
        }
        $value = $row['field_value'] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * Read the form id this run saved.
     */
    private function formId(): int
    {
        $row = QueryUtils::querySingleRow(
            'SELECT form_id FROM forms WHERE pid = ? AND formdir = ? AND deleted = 0',
            [self::PID, self::FORM]
        );
        $this->assertIsArray($row);
        return $this->whole($row['form_id'] ?? null);
    }

    /**
     * Return a whole number from a database cell.
     */
    private function whole(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && preg_match('/^[0-9]+$/', $value) === 1) {
            return (int) $value;
        }
        $this->fail('The id was not a whole number.');
    }


    /**
     * @return array<string, mixed>
     */
    private function storedField(string $fieldId, string $source): array
    {
        return [
            'form_id' => self::FORM,
            'field_id' => $fieldId,
            'source' => $source,
        ];
    }

    /**
     * Delete the rows this run inserted.
     */
    private function removeFixtures(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE d FROM lbf_data d JOIN forms f ON f.form_id = d.form_id WHERE f.pid = ? AND f.formdir = ?',
            [self::PID, self::FORM]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM forms WHERE pid = ? AND formdir = ?', [self::PID, self::FORM]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE pid = ? AND encounter = ?', [self::PID, self::ENCOUNTER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM layout_options WHERE form_id = ?', [self::FORM]);
        QueryUtils::sqlStatementThrowException('DELETE FROM history_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM employer_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM shared_attributes WHERE pid = ?', [self::PID]);
    }
}
