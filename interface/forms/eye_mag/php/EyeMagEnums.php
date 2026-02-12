<?php

/**
 * Enums for the eye_mag form module.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Forms\EyeMag;

/**
 * Subtype filter options for PMSFH and issue queries.
 */
enum SubtypeFilter: string
{
    case Eye = 'eye';
    case Empty = 'empty';
    case None = 'none';

    /**
     * Get the SQL condition fragment and parameter for this filter.
     *
     * @return array{sql: string, params: array}
     */
    public function toSqlCondition(): array
    {
        return match ($this) {
            self::Eye => ['sql' => 'AND subtype = ?', 'params' => ['eye']],
            self::Empty => ['sql' => 'AND (subtype = ? OR subtype IS NULL)', 'params' => ['']],
            self::None => ['sql' => '', 'params' => []],
        };
    }

    /**
     * Get the list_options SQL condition for fallback queries.
     * Used in a_issue.php for quick-pick lists.
     *
     * @return array{sql: string, params: array}
     */
    public function toListOptionsSqlCondition(): array
    {
        return match ($this) {
            self::Eye => ['sql' => 'AND subtype = ?', 'params' => ['eye']],
            self::Empty, self::None => ['sql' => 'AND subtype NOT LIKE ?', 'params' => ['eye']],
        };
    }
}

/**
 * RX type for spectacle prescriptions.
 * Use ->name to get the display string (e.g., RxType::Single->name === 'Single').
 */
enum RxType: string
{
    case Single = '0';
    case Bifocal = '1';
    case Trifocal = '2';
    case Progressive = '3';
}

/**
 * Refraction type for spectacle/contact lens prescriptions.
 */
enum RefType: string
{
    case W = 'W';       // Wearing (current glasses)
    case AR = 'AR';     // Auto-refraction
    case MR = 'MR';     // Manifest refraction
    case CR = 'CR';     // Cycloplegic refraction
    case CTL = 'CTL';   // Contact lens

    /**
     * Get the database column prefix for this refraction type.
     */
    public function columnPrefix(): string
    {
        return $this === self::W ? '' : $this->name;
    }

    /**
     * Get the comments field name for this refraction type.
     */
    public function commentsField(): string
    {
        return match ($this) {
            self::W => 'COMMENTS',
            self::AR, self::MR, self::CR => 'CRCOMMENTS',
            self::CTL => 'COMMENTS',
        };
    }

    /**
     * Get the default checked RX type for this refraction type.
     */
    public function defaultRxType(): ?RxType
    {
        return match ($this) {
            self::AR, self::MR, self::CTL => RxType::Bifocal,
            self::W, self::CR => null,
        };
    }

    /**
     * Check if this refraction type has manufacturer fields (CTL only).
     */
    public function hasManufacturerFields(): bool
    {
        return $this === self::CTL;
    }

    /**
     * Get extra fields mapping for this refraction type.
     * Returns target field => source column mappings.
     *
     * @return array<string, string>
     */
    public function extraFields(): array
    {
        return match ($this) {
            self::W => [],
            self::AR => ['ODADD2' => 'ARODADD', 'OSADD2' => 'AROSADD'],
            self::MR => ['ODADD2' => 'MRODADD', 'OSADD2' => 'MROSADD'],
            self::CR => [],
            self::CTL => [
                'ODBC' => 'CTLODBC', 'ODDIAM' => 'CTLODDIAM', 'ODADD' => 'CTLODADD', 'ODVA' => 'CTLODVA',
                'OSBC' => 'CTLOSBC', 'OSDIAM' => 'CTLOSDIAM', 'OSADD' => 'CTLOSADD', 'OSVA' => 'CTLOSVA',
            ],
        };
    }

    /**
     * Get manufacturer field names for CTL type.
     *
     * @return string[]
     */
    public function manufacturerFields(): array
    {
        return match ($this) {
            self::CTL => [
                'CTLMANUFACTUREROD', 'CTLMANUFACTUREROS',
                'CTLSUPPLIEROD', 'CTLSUPPLIEROS',
                'CTLBRANDOD', 'CTLBRANDOS',
            ],
            default => [],
        };
    }

    /**
     * Get translated human-readable display name for this refraction type.
     */
    public function displayName(): string
    {
        return match ($this) {
            self::W => xlt('Duplicate Rx -- unchanged from current Rx'),
            self::AR => xlt('Auto-Refraction'),
            self::MR => xlt('Manifest (Dry) Refraction'),
            self::CR => xlt('Cycloplegic (Wet) Refraction'),
            self::CTL => xlt('Contact Lens'),
        };
    }

    /**
     * Check if this is a contact lens prescription.
     */
    public function isContactLens(): bool
    {
        return $this === self::CTL;
    }
}

/**
 * Field-based zones for the eye exam form.
 */
enum Zone: string
{
    case EXT = 'EXT';
    case ANTSEG = 'ANTSEG';
    case RETINA = 'RETINA';
    case NEURO = 'NEURO';

    /**
     * Get all zones as an array.
     *
     * @return Zone[]
     */
    public static function all(): array
    {
        return [self::EXT, self::ANTSEG, self::RETINA, self::NEURO];
    }
}

/**
 * Copy-forward operation modes (meta-operations, not field zones).
 */
enum CopyMode: string
{
    case IMPPLAN = 'IMPPLAN';
    case ALL = 'ALL';
    case READONLY = 'READONLY';
}
