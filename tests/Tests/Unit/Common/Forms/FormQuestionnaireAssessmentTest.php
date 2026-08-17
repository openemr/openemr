<?php

/**
 * FormQuestionnaireAssessmentTest exercises the data-mapping contract that
 * FormService::saveEncounterForm() relies on when persisting a questionnaire
 * encounter assessment: the form-table column map and the forms-registry row map.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Common\Forms;

use OpenEMR\Common\Forms\FormQuestionnaireAssessment;
use OpenEMR\Common\Session\SessionUtil;
use PHPUnit\Framework\TestCase;

class FormQuestionnaireAssessmentTest extends TestCase
{
    private const QUESTIONNAIRE_JSON = '{"resourceType":"Questionnaire","id":"q-1"}';
    private const RESPONSE_JSON = '{"resourceType":"QuestionnaireResponse","id":"qr-1"}';

    protected function setUp(): void
    {
        parent::setUp();

        // BaseForm::__construct() reads authUser / authProvider from the active session.
        // Use SessionUtil::setSession() rather than a direct $session->set() (forbidden:
        // direct writes silently fail on read_and_close sessions).
        SessionUtil::setSession([
            'authUser' => 'testuser',
            'authProvider' => 'testprovider',
        ]);
    }

    /**
     * Build a form populated the way the encounter save path populates it before
     * handing it to FormService::saveEncounterForm(). encounter/pid/form_id are typed
     * properties the constructors do not initialize, so they must be set explicitly.
     */
    private function makePopulatedForm(): FormQuestionnaireAssessment
    {
        $form = new FormQuestionnaireAssessment();
        $form->setEncounter(456);
        $form->setPid(7);
        $form->setAuthorized(1);
        $form->setFormId('99');
        $form->setResponseId('resp-uuid');
        $form->setResponseMeta('{"score":1}');
        $form->setQuestionnaireId('42');
        $form->setQuestionnaire(self::QUESTIONNAIRE_JSON);
        $form->setQuestionnaireResponse(self::RESPONSE_JSON);
        $form->setActivity(1);
        $form->setCopyright('© test');
        return $form;
    }

    public function testConstructSetsExpectedDefaults(): void
    {
        $form = new FormQuestionnaireAssessment();

        $this->assertSame('questionnaire_assessments', $form->getFormdir());
        $this->assertSame('questionnaire_assessment', $form->getFormName());
        $this->assertSame(1, $form->getActivity());
        $this->assertSame(0, $form->getAuthorized());
        $this->assertNull($form->getResponseId());
        $this->assertNull($form->getResponseMeta());
        $this->assertNull($form->getQuestionnaireId());
        $this->assertNull($form->getQuestionnaire());
        $this->assertNull($form->getQuestionnaireResponse());
        $this->assertNull($form->getLform());
        $this->assertNull($form->getLformResponse());
    }

    public function testGetFormTableName(): void
    {
        $this->assertSame(
            'form_questionnaire_assessments',
            (new FormQuestionnaireAssessment())->getFormTableName()
        );
    }

    public function testGetFormTableDataForSaveExposesExpectedColumns(): void
    {
        $data = $this->makePopulatedForm()->getFormTableDataForSave();
        $this->assertIsArray($data);

        // The key set must match the columns FormService::saveEncounterForm() will INSERT.
        $this->assertEqualsCanonicalizing(
            [
                'date',
                'response_id',
                'pid',
                'user',
                'groupname',
                'authorized',
                'activity',
                'copyright',
                'form_name',
                'response_meta',
                'questionnaire_id',
                'questionnaire',
                'questionnaire_response',
                'lform',
                'lform_response',
            ],
            array_keys($data),
            'form_questionnaire_assessments column map changed'
        );
    }

    public function testGetFormTableDataForSaveMapsValues(): void
    {
        $data = $this->makePopulatedForm()->getFormTableDataForSave();
        $this->assertIsArray($data);

        $this->assertSame('resp-uuid', $data['response_id']);
        $this->assertSame(7, $data['pid']);
        $this->assertSame(1, $data['authorized']);
        $this->assertSame(1, $data['activity']);
        $this->assertSame('© test', $data['copyright']);
        $this->assertSame('questionnaire_assessment', $data['form_name']);
        $this->assertSame('{"score":1}', $data['response_meta']);
        $this->assertSame('42', $data['questionnaire_id']);
        $this->assertSame(self::QUESTIONNAIRE_JSON, $data['questionnaire']);
        $this->assertSame(self::RESPONSE_JSON, $data['questionnaire_response']);

        $date = $data['date'];
        $this->assertIsString($date);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date);
    }

    /**
     * The FHIR-native runtime does not populate the legacy LForms columns; they must
     * persist as NULL. This pins that so the retired representation cannot silently
     * come back through this save path.
     */
    public function testLformColumnsRemainNull(): void
    {
        $data = $this->makePopulatedForm()->getFormTableDataForSave();
        $this->assertIsArray($data);

        $this->assertArrayHasKey('lform', $data);
        $this->assertArrayHasKey('lform_response', $data);
        $this->assertNull($data['lform']);
        $this->assertNull($data['lform_response']);
    }

    public function testGetEncounterFormDataForSavePopulatesRegistryRow(): void
    {
        $row = $this->makePopulatedForm()->getEncounterFormDataForSave();

        $this->assertEqualsCanonicalizing(
            [
                'date',
                'encounter',
                'form_name',
                'form_id',
                'pid',
                'user',
                'groupName',
                'authorized',
                'formdir',
                'therapy_group_id',
            ],
            array_keys($row),
            'forms registry row map changed'
        );

        $this->assertSame(456, $row['encounter']);
        $this->assertSame('99', $row['form_id']);
        $this->assertSame(7, $row['pid']);
        $this->assertSame('questionnaire_assessments', $row['formdir']);
        $this->assertSame('questionnaire_assessment', $row['form_name']);
        $this->assertSame(1, $row['authorized']);
    }

    public function testSettersReturnSameInstanceForChaining(): void
    {
        $form = new FormQuestionnaireAssessment();

        $this->assertSame($form, $form->setResponseId('x'));
        $this->assertSame($form, $form->setQuestionnaire(self::QUESTIONNAIRE_JSON));
    }
}
