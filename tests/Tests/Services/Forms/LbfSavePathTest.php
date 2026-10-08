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

    protected function setUp(): void
    {
        $this->savedPost = $_POST;
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

    protected function tearDown(): void
    {
        $_POST = $this->savedPost;
        $this->removeFixtures();
    }

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
        $this->assertSame('ZZ visit', $this->encounterValue('reason'));
        $this->assertSame('ZZ city', $this->patientValue('city'));
        $this->assertSame('ZZ note', $this->noteValue());
        $this->assertSame(self::PID, $this->patientId());
    }

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

        $this->assertSame('ZZ visit 2', $this->encounterValue('reason'));
        $this->assertSame('ZZ city 2', $this->patientValue('city'));
        $this->assertNull($this->noteValue());
    }

    #[Test]
    public function testARefusedColumnStopsTheSaveAndLeavesTheRowKey(): void
    {
        $this->layoutField('pid', 'D', 1, 2, '');
        $this->postForm([
            'form_pid' => '1',
            'form_reason' => 'ZZ should not land',
            'bn_save' => 'Save',
        ]);

        $this->assertSame('before', $this->encounterValue('reason'));
        $this->assertSame(self::PID, $this->patientId());
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function postForm(array $fields, int $formId = 0): string
    {
        $cookies = new CookieJar();
        $base = 'http://127.0.0.1';
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
        ]);
        $response = $fields === []
            ? $client->get($url)
            : $client->post($url, ['form_params' => $fields]);

        return (string) $response->getBody();
    }

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

    private function encounterValue(string $column): string
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

    private function patientValue(string $column): string
    {
        $row = QueryUtils::querySingleRow('SELECT city FROM patient_data WHERE pid = ?', [self::PID]);
        $this->assertIsArray($row);
        $value = $row['city'] ?? null;
        $this->assertIsString($value);

        return $value;
    }

    private function patientId(): int
    {
        $row = QueryUtils::querySingleRow('SELECT pid FROM patient_data WHERE fname = ? AND lname = ?', ['ZZ', 'Layout']);
        $this->assertIsArray($row);
        return $this->whole($row['pid'] ?? null);
    }

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

    private function formId(): int
    {
        $row = QueryUtils::querySingleRow(
            'SELECT form_id FROM forms WHERE pid = ? AND formdir = ? AND deleted = 0',
            [self::PID, self::FORM]
        );
        $this->assertIsArray($row);
        return $this->whole($row['form_id'] ?? null);
    }

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
    }
}
