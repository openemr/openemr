<?php

/**
 * Why a patient app write was refused, with the HTTP status and FHIR issue type it is answered
 * with.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

enum PatientWriteRefusalReason
{
    /** The practice's mode or type settings don't allow this client to write this type. */
    case NotAllowed;
    /** The resource names a patient other than the token's. */
    case OtherPatient;
    /** The resource isn't the type it was sent as (or a non-vital-sign Observation). */
    case WrongType;
    /** The resource is over the size a held write may be. */
    case TooLarge;
    /** The app already has as many writes waiting for review for the patient as it may. */
    case TooManyOpen;
    /** The resource refers to something this server doesn't have, so it could never be approved. */
    case NotWritable;

    /**
     * The HTTP status the app is answered with.
     */
    public function httpStatus(): int
    {
        return match ($this) {
            self::NotAllowed, self::OtherPatient => 403,
            self::WrongType, self::NotWritable => 400,
            self::TooLarge => 413,
            self::TooManyOpen => 429,
        };
    }

    /**
     * The OperationOutcome issue code (http://hl7.org/fhir/issue-type).
     */
    public function issueCode(): string
    {
        return match ($this) {
            self::NotAllowed, self::OtherPatient => 'forbidden',
            self::WrongType => 'invalid',
            self::NotWritable => 'not-found',
            self::TooLarge => 'too-costly',
            self::TooManyOpen => 'throttled',
        };
    }

    /**
     * Text for the app's OperationOutcome. It names the practice setting or the limit, never
     * anything about the server.
     */
    public function diagnostics(): string
    {
        return match ($this) {
            self::NotAllowed => 'This practice does not accept this resource type from this kind of app. The practice controls this in its patient app write settings.',
            self::OtherPatient => 'The resource must be for the patient this access token is for',
            self::WrongType => 'The resource is not the type this endpoint accepts from patient apps',
            self::TooLarge => 'The resource is larger than ' . intdiv(PatientWriteHold::MAX_PAYLOAD_BYTES, 1024) . ' KB',
            self::TooManyOpen => 'This app already has ' . PatientWriteHold::MAX_OPEN_PER_CLIENT . ' writes waiting for review for this patient',
            self::NotWritable => 'The resource refers to something this server does not have. A QuestionnaireResponse must answer a Questionnaire on this server, at its current version, and any encounter or caregiver (RelatedPerson) source must be this patient\'s.',
        };
    }
}
