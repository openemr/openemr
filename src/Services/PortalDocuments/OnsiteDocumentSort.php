<?php

/**
 * The columns a portal document list may be sorted by. The API names them as the old Phreeze
 * property names (`orderBy=DocType`); the list is always sorted by review date after that.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PortalDocuments;

enum OnsiteDocumentSort: string
{
    case Id = 'Id';
    case Pid = 'Pid';
    case Facility = 'Facility';
    case Provider = 'Provider';
    case Encounter = 'Encounter';
    case CreateDate = 'CreateDate';
    case DocType = 'DocType';
    case PatientSignedStatus = 'PatientSignedStatus';
    case PatientSignedTime = 'PatientSignedTime';
    case AuthorizeSignedTime = 'AuthorizeSignedTime';
    case AcceptSignedStatus = 'AcceptSignedStatus';
    case AuthorizingSignator = 'AuthorizingSignator';
    case ReviewDate = 'ReviewDate';
    case DenialReason = 'DenialReason';
    case FileName = 'FileName';
    case FilePath = 'FilePath';

    /**
     * Parse the API's `orderBy` value. An empty value means the default, the denial reason.
     *
     * @throws \InvalidArgumentException for a name that is not a sortable column
     */
    public static function fromRequest(string $orderBy): self
    {
        if ($orderBy === '') {
            return self::DenialReason;
        }

        return self::tryFrom($orderBy) ?? throw new \InvalidArgumentException('Unknown portal document sort column');
    }

    /**
     * The onsite_documents column this sort orders by.
     */
    public function column(): string
    {
        return match ($this) {
            self::Id => 'id',
            self::Pid => 'pid',
            self::Facility => 'facility',
            self::Provider => 'provider',
            self::Encounter => 'encounter',
            self::CreateDate => 'create_date',
            self::DocType => 'doc_type',
            self::PatientSignedStatus => 'patient_signed_status',
            self::PatientSignedTime => 'patient_signed_time',
            self::AuthorizeSignedTime => 'authorize_signed_time',
            self::AcceptSignedStatus => 'accept_signed_status',
            self::AuthorizingSignator => 'authorizing_signator',
            self::ReviewDate => 'review_date',
            self::DenialReason => 'denial_reason',
            self::FileName => 'file_name',
            self::FilePath => 'file_path',
        };
    }
}
