<?php

/**
 * What the loader needs to know about a related_person_relationship
 * option id to make up a plausible person: the kind of relation and, when
 * the code says, the person's sex.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

enum RelationshipKind
{
    case Spouse;
    case Parent;
    case Child;
    case Sibling;
    case Grandparent;
    case OtherFamily;
    case NotFamily;

    private const SPOUSE = ['SPS', 'HUSB', 'WIFE', 'DOMPART', 'SIGOTHR', 'FMRSPS'];
    private const PARENT = [
        'PRN', 'NPRN', 'FTH', 'NFTH', 'MTH', 'NMTH', 'ADOPTF', 'ADOPTM', 'ADOPTP', 'FTHFOST', 'MTHFOST',
        'PRNFOST', 'STPFTH', 'STPMTH', 'STPPRN', 'GESTM',
    ];
    private const CHILD = [
        'CHILD', 'NCHILD', 'DAUC', 'DAU', 'SONC', 'SON', 'CHLDADOPT', 'DAUADOPT', 'SONADOPT', 'CHLDFOST',
        'DAUFOST', 'SONFOST', 'STPCHLD', 'STPDAU', 'STPSON',
    ];
    private const GRANDPARENT = ['GRPRN', 'GRFTH', 'GRMTH', 'MGRPRN', 'MGRFTH', 'MGRMTH', 'PGRPRN', 'PGRFTH', 'PGRMTH'];
    private const NOT_FAMILY = ['FRND', 'NBOR', 'ROOM'];
    private const FEMALE = [
        'WIFE', 'MTH', 'NMTH', 'ADOPTM', 'MTHFOST', 'STPMTH', 'GESTM', 'DAUC', 'DAU', 'DAUADOPT', 'DAUFOST',
        'STPDAU', 'SIS', 'NSIS', 'HSIS', 'STPSIS', 'TWINSIS', 'FTWINSIS', 'ITWINSIS', 'GRMTH', 'MGRMTH',
        'PGRMTH', 'GGRMTH', 'MGGRMTH', 'PGGRMTH', 'GRNDDAU', 'AUNT', 'MAUNT', 'PAUNT', 'NIECE', 'MTHINLAW',
        'SISINLAW', 'DAUINLAW',
    ];
    private const MALE = [
        'HUSB', 'FTH', 'NFTH', 'ADOPTF', 'FTHFOST', 'STPFTH', 'SONC', 'SON', 'SONADOPT', 'SONFOST', 'STPSON',
        'BRO', 'NBRO', 'HBRO', 'STPBRO', 'TWINBRO', 'FTWINBRO', 'ITWINBRO', 'GRFTH', 'MGRFTH', 'PGRFTH',
        'GGRFTH', 'MGGRFTH', 'PGGRFTH', 'GRNDSON', 'UNCLE', 'MUNCLE', 'PUNCLE', 'NEPHEW', 'FTHINLAW',
        'BROINLAW', 'SONINLAW',
    ];

    public static function of(string $relationship): self
    {
        return match (true) {
            in_array($relationship, self::SPOUSE, true) => self::Spouse,
            in_array($relationship, self::PARENT, true) => self::Parent,
            in_array($relationship, self::CHILD, true) => self::Child,
            in_array($relationship, self::GRANDPARENT, true) => self::Grandparent,
            str_contains($relationship, 'SIB') || str_contains($relationship, 'BRO')
                || str_contains($relationship, 'SIS') || str_contains($relationship, 'TWIN') => self::Sibling,
            in_array($relationship, self::NOT_FAMILY, true) => self::NotFamily,
            default => self::OtherFamily,
        };
    }

    /**
     * The sex the relationship code implies: list `sex` option id, or null.
     */
    public static function sexOf(string $relationship): ?string
    {
        return match (true) {
            in_array($relationship, self::FEMALE, true) => 'Female',
            in_array($relationship, self::MALE, true) => 'Male',
            default => null,
        };
    }

    /**
     * Takes the patient's last name when the seed file doesn't give one.
     */
    public function sharesLastName(): bool
    {
        return match ($this) {
            self::Spouse, self::Parent, self::Child, self::Sibling => true,
            self::Grandparent, self::OtherFamily, self::NotFamily => false,
        };
    }

    /**
     * Whether a patient of this age can plausibly have this relation as an
     * adult contact: no adult children under 38, no living parents over 75,
     * no living grandparents over 45.
     */
    public function fitsPatientAge(int $age): bool
    {
        return match ($this) {
            self::Child => $age >= 38,
            self::Parent => $age <= 75,
            self::Spouse => $age >= 18,
            self::Grandparent => $age <= 45,
            self::Sibling, self::OtherFamily, self::NotFamily => true,
        };
    }
}
