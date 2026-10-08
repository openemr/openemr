<?php

/**
 * Creating a layout form, then editing it and saving values.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Forms;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LbfCreateAndEditTest extends TestCase
{
    private const PID = 99055088;

    private const ENCOUNTER = 99055088;

    private const FORM = 'LBFZZMade';

    private const BASE = 'http://127.0.0.1';

    protected function setUp(): void
    {
        $this->removeFixtures();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, fname, lname, DOB, sex, pubpid, city) VALUES (?, 'ZZ', 'Made', '1980-01-01', 'Male', ?, 'before')",
            [self::PID, 'ZZ' . self::PID]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO form_encounter (pid, encounter, date, reason, provider_id, facility_id, pc_catid) VALUES (?, ?, NOW(), 'before', 1, 3, 5)",
            [self::PID, self::ENCOUNTER]
        );
    }

    protected function tearDown(): void
    {
        $this->removeFixtures();
    }

    #[Test]
    public function testALayoutCanBeCreatedEditedAndSaved(): void
    {
        $cookies = $this->login();
        $this->createLayout($cookies, 'ZZ Created');
        $this->addGroup($cookies, 'ZZ Group');
        $this->addField($cookies, 'ZZNote', 'ZZ Note', '', '2', '1');
        $this->addField($cookies, 'reason', 'ZZ Reason', 'V', '2', '1');

        $this->openForm($cookies, [
            'form_ZZNote' => 'ZZ first',
            'form_reason' => 'ZZ visit',
            'bn_save' => 'Save',
        ]);
        $this->assertSame('ZZ first', $this->noteValue());
        $this->assertSame('ZZ visit', $this->encounterReason());
        $this->assertSame(1, $this->formCount());

        $formId = $this->formId();
        $this->openForm($cookies, [
            'form_ZZNote' => 'ZZ second',
            'form_reason' => 'ZZ visit 2',
            'bn_save' => 'Save',
        ], $formId);
        $this->assertSame('ZZ second', $this->noteValue());
        $this->assertSame('ZZ visit 2', $this->encounterReason());
        $this->assertSame(1, $this->formCount());

        $this->renameField($cookies, 'ZZNote', 'ZZ Note edited');
        $title = QueryUtils::querySingleRow(
            'SELECT title FROM layout_options WHERE form_id = ? AND field_id = ?',
            [self::FORM, 'ZZNote']
        );
        $this->assertIsArray($title);
        $this->assertSame('ZZ Note edited', $title['title']);
        $this->assertSame('ZZ second', $this->noteValue());

        $this->openForm($cookies, [
            'form_ZZNote' => 'ZZ third',
            'form_reason' => 'ZZ visit 2',
            'bn_save' => 'Save',
        ], $formId);
        $this->assertSame('ZZ third', $this->noteValue());
    }

    #[Test]
    public function testAGroupWithoutPropertiesStillSaves(): void
    {
        $cookies = $this->login();
        $this->createLayout($cookies, 'ZZ Created');
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO layout_options
                (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description, fld_rows, source)
             VALUES (?, ?, ?, ?, ?, ?, 1, 0, 0, ?, 1, 1, ?, ?, ?, 0, ?)',
            [self::FORM, 'Loose', '9', 'Loose', 10, 2, '', '', '', '', '']
        );

        $body = $this->openForm($cookies, [
            'form_Loose' => 'ZZ loose',
            'bn_save' => 'Save',
        ]);

        $this->assertStringNotContainsString('query failed', strtolower($body));
        $this->assertSame('ZZ loose', $this->fieldValue('Loose'));
    }

    private function login(): CookieJar
    {
        $cookies = new CookieJar();
        $this->request($cookies, self::BASE . '/interface/login/login.php?site=default');
        $this->request($cookies, self::BASE . '/interface/main/main_screen.php?auth=login&site=default', [
            'authUser' => 'admin',
            'clearPass' => 'pass',
            'new_login_session_management' => '1',
            'languageChoice' => '1',
        ]);
        $this->request(
            $cookies,
            self::BASE . '/interface/patient_file/encounter/encounter_top.php?set_pid=' . self::PID . '&set_encounter=' . self::ENCOUNTER
        );

        return $cookies;
    }

    private function createLayout(CookieJar $cookies, string $title): void
    {
        $page = $this->request($cookies, self::BASE . '/interface/super/edit_layout_props.php');
        $this->request($cookies, self::BASE . '/interface/super/edit_layout_props.php', [
            'csrf_token_form' => $this->token($page),
            'form_submit' => 'Submit',
            'form_form_id' => self::FORM,
            'form_title' => $title,
            'form_subtitle' => '',
            'form_mapping' => 'Clinical',
            'form_seq' => '0',
            'form_activity' => '1',
            'form_repeats' => '0',
            'form_columns' => '4',
            'form_size' => '9',
            'form_issue' => '',
            'form_aco' => '',
            'form_init_open' => '1',
            'form_services_codes' => '',
            'form_products_codes' => '',
            'form_diags_codes' => '',
        ]);

        $row = QueryUtils::querySingleRow(
            "SELECT grp_title FROM layout_group_properties WHERE grp_form_id = ? AND grp_group_id = ''",
            [self::FORM]
        );
        $this->assertIsArray($row);
        $this->assertSame($title, $row['grp_title']);
    }

    private function addGroup(CookieJar $cookies, string $name): void
    {
        $page = $this->request($cookies, self::BASE . '/interface/super/edit_layout.php?layout_id=' . self::FORM);
        $this->request($cookies, self::BASE . '/interface/super/edit_layout.php?layout_id=' . self::FORM, [
            'csrf_token_form' => $this->token($page),
            'formaction' => 'addgroup',
            'layout_id' => self::FORM,
            'newgroupname' => $name,
            'newgroupparent' => '',
        ]);

        $row = QueryUtils::querySingleRow(
            'SELECT grp_group_id FROM layout_group_properties WHERE grp_form_id = ? AND grp_title = ?',
            [self::FORM, $name]
        );
        $this->assertIsArray($row);
        $this->assertSame('1', $row['grp_group_id']);
    }

    private function addField(
        CookieJar $cookies,
        string $fieldId,
        string $title,
        string $source,
        string $dataType,
        string $groupId
    ): void {
        $page = $this->request($cookies, self::BASE . '/interface/super/edit_layout.php?layout_id=' . self::FORM);
        $this->request($cookies, self::BASE . '/interface/super/edit_layout.php?layout_id=' . self::FORM, [
            'csrf_token_form' => $this->token($page),
            'formaction' => 'addfield',
            'layout_id' => self::FORM,
            'newid' => $fieldId,
            'newtitle' => $title,
            'newsource' => $source,
            'newdatatype' => $dataType,
            'newfieldgroupid' => $groupId,
            'newseq' => '10',
            'newuor' => '1',
            'newlengthWidth' => '20',
            'newlengthHeight' => '1',
            'newtitlecols' => '1',
            'newdatacols' => '1',
            'newdefault' => '',
            'newcodes' => '',
            'newdesc' => '',
            'newmaxSize' => '255',
            'newlistid' => '',
            'newbackuplistid' => '',
        ]);
    }

    private function renameField(CookieJar $cookies, string $fieldId, string $title): void
    {
        $page = $this->request($cookies, self::BASE . '/interface/super/edit_layout.php?layout_id=' . self::FORM);
        $this->request($cookies, self::BASE . '/interface/super/edit_layout.php?layout_id=' . self::FORM, [
            'csrf_token_form' => $this->token($page),
            'formaction' => 'save',
            'layout_id' => self::FORM,
            'fld' => [
                1 => [
                    'id' => $fieldId,
                    'originalid' => $fieldId,
                    'source' => '',
                    'title' => $title,
                    'group' => '1',
                    'seq' => '10',
                    'uor' => '1',
                    'lengthWidth' => '20',
                    'lengthHeight' => '1',
                    'maxSize' => '255',
                    'titlecols' => '1',
                    'datacols' => '1',
                    'datatype' => '2',
                    'list_id' => '',
                    'list_backup_id' => '',
                    'default' => '',
                    'desc' => '',
                    'codes' => '',
                    'validation' => '',
                ],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function openForm(CookieJar $cookies, array $fields, int $formId = 0): string
    {
        return $this->request(
            $cookies,
            self::BASE . '/interface/forms/LBF/new.php?formname=' . self::FORM . '&id=' . $formId,
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

    private function token(string $page): string
    {
        if (preg_match('/name="csrf_token_form" value="([^"]+)"/', $page, $match) !== 1) {
            $this->fail('The layout page did not offer a token.');
        }

        return html_entity_decode($match[1], ENT_QUOTES);
    }

    private function noteValue(): string
    {
        return $this->fieldValue('ZZNote');
    }

    private function fieldValue(string $fieldId): string
    {
        $row = QueryUtils::querySingleRow(
            'SELECT d.field_value FROM lbf_data d JOIN forms f ON f.form_id = d.form_id WHERE f.pid = ? AND f.formdir = ? AND d.field_id = ? AND f.deleted = 0',
            [self::PID, self::FORM, $fieldId]
        );
        $this->assertIsArray($row);
        $value = $row['field_value'] ?? null;
        $this->assertIsString($value);

        return $value;
    }

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

    private function formCount(): int
    {
        $row = QueryUtils::querySingleRow(
            'SELECT COUNT(*) AS c FROM forms WHERE pid = ? AND formdir = ? AND deleted = 0',
            [self::PID, self::FORM]
        );
        $this->assertIsArray($row);

        return $this->whole($row['c'] ?? null);
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
        QueryUtils::sqlStatementThrowException('DELETE FROM layout_group_properties WHERE grp_form_id = ?', [self::FORM]);
    }
}
