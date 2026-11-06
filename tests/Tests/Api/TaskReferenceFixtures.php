<?php

/**
 * Records a Task can name outside the patient's record (a Questionnaire, an Organization, a
 * Practitioner, a PractitionerRole), made for a test and removed after it, with what saving them
 * wrote elsewhere. A task's references to these have to exist on the server, so a test can't name
 * made-up ones.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\AddressBookOrganizationService;
use OpenEMR\Services\QuestionnaireService;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use PHPUnit\Framework\Assert;

final class TaskReferenceFixtures
{
    private ?string $questionnaireTitle = null;
    private ?string $practitionerUuid = null;
    private ?string $organizationUuid = null;
    private ?string $practitionerRoleUuid = null;
    private ?string $userWithoutNpiUuid = null;
    private ?string $addressBookOrganizationUuid = null;
    private ?PractitionerFixtureManager $practitioners = null;
    private ?FacilityFixtureManager $facilities = null;

    public function __construct(private readonly ApiTestClient $client)
    {
    }

    /** A Questionnaire saved for this run, by uuid. */
    public function questionnaire(): string
    {
        $data = json_decode((string)file_get_contents(__DIR__ . '/../Fixtures/FHIR/questionnaire.json'), true);
        $questionnaire = is_array($data) ? ($data[0] ?? null) : null;
        Assert::assertIsArray($questionnaire);
        $this->questionnaireTitle ??= 'test-fixture Task reference Questionnaire ' . bin2hex(random_bytes(4));
        $questionnaire['title'] = $this->questionnaireTitle;
        unset($questionnaire['id'], $questionnaire['url']);
        $rowId = (new QuestionnaireService())->saveQuestionnaireResource($questionnaire);
        Assert::assertNotEmpty($rowId);
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM questionnaire_repository WHERE id = ?', 'uuid', [$rowId]);
        Assert::assertIsString($uuid);
        return UuidRegistry::uuidToString($uuid);
    }

    /** A Practitioner (a user with an NPI) from the practitioner fixtures. */
    public function practitioner(): string
    {
        if ($this->practitionerUuid === null) {
            $this->practitioners ??= new PractitionerFixtureManager();
            $this->practitioners->installPractitionerFixtures();
            $this->practitionerUuid = self::uuidOf(
                "SELECT uuid FROM users WHERE fname LIKE 'test-fixture-%' AND npi IS NOT NULL AND npi != '' ORDER BY id DESC LIMIT 1"
            );
        }
        return $this->practitionerUuid;
    }

    /**
     * A user with no NPI. FHIR's Practitioner search leaves such users out, but core names them as
     * Practitioners (Patient.generalPractitioner, CarePlan.author), so a task can too.
     */
    public function practitionerWithoutNpi(): string
    {
        if ($this->userWithoutNpiUuid === null) {
            $uuid = (new UuidRegistry(['table_name' => 'users']))->createUuid();
            QueryUtils::sqlInsert("INSERT INTO users (uuid, fname, lname, npi, active) VALUES (?, 'test-fixture-no-npi', 'Practitioner', '', 1)", [$uuid]);
            $this->userWithoutNpiUuid = UuidRegistry::uuidToString($uuid);
        }
        return $this->userWithoutNpiUuid;
    }

    /** An address book organization (a users row FHIR serves as an Organization, not a Practitioner). */
    public function addressBookOrganization(): string
    {
        if ($this->addressBookOrganizationUuid === null) {
            $uuid = (new UuidRegistry(['table_name' => 'users']))->createUuid();
            QueryUtils::sqlInsert(
                "INSERT INTO users (uuid, organization, abook_type, active) VALUES (?, 'test-fixture Address Book Organization', ?, 1)",
                [$uuid, AddressBookOrganizationService::ABOOK_TYPE]
            );
            $this->addressBookOrganizationUuid = UuidRegistry::uuidToString($uuid);
        }
        return $this->addressBookOrganizationUuid;
    }

    /** An Organization (a facility) from the facility fixtures. */
    public function organization(): string
    {
        if ($this->organizationUuid === null) {
            $this->facilities ??= new FacilityFixtureManager();
            $this->facilities->installFacilityFixtures();
            $this->organizationUuid = self::uuidOf("SELECT uuid FROM facility WHERE name LIKE 'test-fixture%' ORDER BY id DESC LIMIT 1");
        }
        return $this->organizationUuid;
    }

    /** A PractitionerRole for the practitioner at the organization, made through the API. */
    public function practitionerRole(): string
    {
        if ($this->practitionerRoleUuid === null) {
            $data = json_decode((string)file_get_contents(__DIR__ . '/../Fixtures/FHIR/practitioner-role.json'), true);
            $role = is_array($data) ? ($data[0] ?? null) : null;
            Assert::assertIsArray($role);
            $role['practitioner'] = ['reference' => 'Practitioner/' . $this->practitioner()];
            $role['organization'] = ['reference' => 'Organization/' . $this->organization()];
            unset($role['id']);
            $body = [];
            foreach ($role as $key => $value) {
                Assert::assertIsString($key);
                $body[$key] = $value;
            }
            $response = $this->client->post('/apis/default/fhir/PractitionerRole', $body);
            $contents = $response->getBody()->getContents();
            $json = json_decode($contents, true);
            $uuid = is_array($json) ? ($json['uuid'] ?? $json['id'] ?? null) : null;
            Assert::assertIsString($uuid, 'PractitionerRole created. Body: ' . $contents);
            $this->practitionerRoleUuid = $uuid;
        }
        return $this->practitionerRoleUuid;
    }

    public function remove(): void
    {
        if ($this->questionnaireTitle !== null) {
            $title = [$this->questionnaireTitle];
            // Everything saving the questionnaire and answering it wrote: the dashboard assignment
            // saveQuestionnaireResource() gives every new questionnaire, the responses, and the uuids.
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM content_assignments WHERE resource_type = 'questionnaire' AND resource_id IN (SELECT id FROM questionnaire_repository WHERE name = ?)",
                $title
            );
            QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM questionnaire_response WHERE questionnaire_name = ?)', $title);
            QueryUtils::sqlStatementThrowException('DELETE FROM questionnaire_response WHERE questionnaire_name = ?', $title);
            QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM questionnaire_repository WHERE name = ?)', $title);
            QueryUtils::sqlStatementThrowException('DELETE FROM questionnaire_repository WHERE name = ?', $title);
        }
        foreach ([$this->userWithoutNpiUuid, $this->addressBookOrganizationUuid] as $user) {
            if ($user !== null) {
                $uuid = [UuidRegistry::uuidToBytes($user)];
                QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE uuid = ?', $uuid);
                QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE uuid = ?', $uuid);
            }
        }
        if ($this->practitionerRoleUuid !== null) {
            (new FixtureManager())->removePractitionerRoleFixtures();
        }
        $this->practitioners?->removePractitionerFixtures();
        $this->facilities?->removeInstalledFixtures();
    }

    private static function uuidOf(string $sql): string
    {
        $row = QueryUtils::querySingleRow($sql, []);
        Assert::assertIsArray($row);
        Assert::assertIsString($row['uuid'] ?? null);
        return UuidRegistry::uuidToString($row['uuid']);
    }
}
