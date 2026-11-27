<?php

/**
 * Adds an approved patient app questionnaire response to an encounter as a Questionnaire
 * Assessment form, the form staff fill in for a questionnaire at a visit.
 *
 * The response is already in questionnaire_response; the form row points at it by response id,
 * so the encounter shows it like any other questionnaire answered there, and FHIR reads give the
 * response its encounter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Forms\FormQuestionnaireAssessment;
use OpenEMR\Services\FormService;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewActorType;
use OpenEMR\Services\QuestionnaireResponseService;

final readonly class QuestionnaireResponseEncounterForm
{
    public function __construct(
        private QuestionnaireResponseService $responses,
        private FormService $forms,
    ) {
    }

    /**
     * Adds the stored response to the encounter, with the approving staff user as the user who
     * added the form. Runs inside the approval's transaction.
     *
     * @param string $responseUuid the stored response's uuid, which is also its response id
     * @param int $encounter form_encounter.encounter, already checked to be the patient's
     * @return int the forms row id
     * @throws \RuntimeException when the response isn't stored or the reviewer isn't a user
     */
    public function add(string $responseUuid, int $pid, int $encounter, ReviewActor $reviewer): int
    {
        if ($reviewer->type !== ReviewActorType::User || $reviewer->id === null) {
            throw new \RuntimeException('Only a staff user can add a response to an encounter');
        }
        $stored = $this->responses->fetchQuestionnaireResponseByResponseId($responseUuid);
        if (($stored['response_id'] ?? null) !== $responseUuid || self::int($stored['patient_id'] ?? null) !== $pid) {
            throw new \RuntimeException('The approved response is not in the chart');
        }
        $responseJson = self::text($stored['questionnaire_response'] ?? null);
        $meta = $this->responses->extractResponseMetaData($responseJson, true);

        $form = new FormQuestionnaireAssessment();
        $form->setPid($pid);
        $form->setEncounter($encounter);
        $form->setTherapyGroupId(null);
        $form->setUser($reviewer->name);
        $form->setGroupname(self::groupOf($reviewer->name));
        $form->setAuthorized(self::isAuthorized($reviewer->id) ? 1 : 0);
        $name = self::text($stored['questionnaire_name'] ?? null);
        $form->setFormName($name !== '' ? $name : 'Questionnaire Assessment');
        $form->setResponseId($responseUuid);
        $form->setResponseMeta(is_string($meta) ? $meta : null);
        $form->setQuestionnaire(self::text($stored['questionnaire'] ?? null));
        $form->setQuestionnaireResponse($responseJson);
        $questionnaireId = self::int($stored['questionnaire_foreign_id'] ?? null);
        if ($questionnaireId !== null) {
            $form->setQuestionnaireId((string) $questionnaireId);
        }

        $saved = $this->forms->saveEncounterForm($form);
        $id = $saved->getId();
        if ($id === null || $id <= 0) {
            throw new \RuntimeException('The encounter form could not be added');
        }

        return $id;
    }

    /**
     * The provider group the user signs in under, as the session's authProvider holds it.
     */
    private static function groupOf(string $username): ?string
    {
        $group = QueryUtils::fetchSingleValue('SELECT `name` FROM `groups` WHERE BINARY `user` = ?', 'name', [$username]);

        return is_string($group) && $group !== '' ? $group : null;
    }

    /**
     * Whether the user is an authorized provider, as the session's userauthorized holds it.
     */
    private static function isAuthorized(int $userId): bool
    {
        return self::int(QueryUtils::fetchSingleValue('SELECT `authorized` FROM `users` WHERE `id` = ?', 'authorized', [$userId])) === 1;
    }

    private static function int(mixed $value): ?int
    {
        return is_int($value) ? $value : (is_string($value) && ctype_digit($value) ? intval($value) : null);
    }

    private static function text(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
