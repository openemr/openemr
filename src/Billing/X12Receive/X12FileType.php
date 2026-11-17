<?php

/**
 * The kinds of X12 file an X12 partner sends back over SFTP.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

/**
 * Backed because the value is stored in x12_remote_received.file_type and is
 * the EDI History type name.
 */
enum X12FileType: string
{
    case Remittance835 = 'f835';
    case ClaimStatus277 = 'f277';
    case Acknowledgment999 = 'f997';

    /**
     * The functional group code (GS01) a file of this kind carries.
     */
    public function groupCode(): string
    {
        return match ($this) {
            self::Remittance835 => 'HP',
            self::ClaimStatus277 => 'HN',
            self::Acknowledgment999 => 'FA',
        };
    }

    /**
     * Whether a file whose envelope reports $code (X12Envelope::groupCode()) is of this
     * kind. An interchange acknowledgment (TA1) with no functional group counts as an
     * acknowledgment, as it does in EDI History.
     */
    public function accepts(?string $code): bool
    {
        return $code === $this->groupCode()
            || ($this === self::Acknowledgment999 && $code === X12Envelope::INTERCHANGE_ACK);
    }

    /**
     * @param list<self> $candidates
     */
    public static function forGroupCode(?string $code, array $candidates): ?self
    {
        foreach ($candidates as $candidate) {
            if ($candidate->accepts($code)) {
                return $candidate;
            }
        }
        return null;
    }

    /**
     * The x12_partners column that names the remote folder to read.
     */
    public function partnerColumn(): string
    {
        return match ($this) {
            self::Remittance835 => 'x12_sftp_receive_dir_835',
            self::ClaimStatus277 => 'x12_sftp_receive_dir_277',
            self::Acknowledgment999 => 'x12_sftp_receive_dir_999',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Remittance835 => '835',
            self::ClaimStatus277 => '277',
            self::Acknowledgment999 => '999',
        };
    }
}
