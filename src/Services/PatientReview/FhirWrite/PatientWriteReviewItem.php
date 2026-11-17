<?php

/**
 * One held patient app write as the staff review screen shows it.
 *
 * Everything here except the ids came from the patient or the app, so the screen escapes all
 * of it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\Services\PatientReview\ReviewRequest;

final readonly class PatientWriteReviewItem
{
    /**
     * @param ?string $appName oauth_clients.client_name; null when the client has been removed
     * @param ?PatientWriteClientType $clientType null when the client has been removed
     * @param ?int $duplicateOf an earlier pending request for the patient with the same content
     * @param list<array{question: string, answers: list<string>, unshown: int}> $answers a
     *        questionnaire response's answers, with how many of each question's answers are of a
     *        type the screen doesn't show; empty for other types
     * @param int $answersTooDeep answers nested too deeply for any row to show them
     * @param string $resourceJson the held resource, pretty-printed
     * @param bool $canApprove whether this type can be approved yet
     * @param ?string $questionnaireName the name of the Questionnaire a response answers, when it
     *                                   is stored on this server
     */
    public function __construct(
        public ReviewRequest $request,
        public string $patientName,
        public string $pubpid,
        public ?string $appName,
        public ?PatientWriteClientType $clientType,
        public ?int $duplicateOf,
        public array $answers,
        public string $resourceJson,
        public bool $canApprove,
        public ?string $questionnaireName = null,
        public int $answersTooDeep = 0,
    ) {
    }
}
