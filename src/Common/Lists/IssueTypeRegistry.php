<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Lists;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

/**
 * Lazy loader for the `issue_types` lookup tables that legacy code reads as
 * top-level globals ($ISSUE_TYPES, $ISSUE_TYPE_CATEGORIES, $ISSUE_TYPE_STYLES,
 * $ISSUE_CLASSIFICATIONS). Each getter memoizes its result and writes it into
 * OEGlobalsBag on first call so `global $ISSUE_TYPES;` and
 * `OEGlobalsBag::get('ISSUE_TYPES')` readers continue to work while callers
 * migrate.
 */
final class IssueTypeRegistry
{
    /** @var array<string, string>|null */
    private static ?array $categories = null;

    /** @var array<int, string>|null */
    private static ?array $styles = null;

    /** @var array<string, array{string, string, string, mixed, mixed, mixed}>|null */
    private static ?array $types = null;

    /** @var array<int, string>|null */
    private static ?array $classifications = null;

    /**
     * @return array<string, string>
     */
    public static function issueTypeCategories(): array
    {
        if (self::$categories !== null) {
            return self::$categories;
        }

        $categories = [
            'default' => xl('Default'),
            'ippf_specific' => xl('IPPF'),
        ];
        foreach (QueryUtils::fetchRecords("SELECT DISTINCT `category` FROM `issue_types`") as $row) {
            $category = is_string($row['category'] ?? null) ? $row['category'] : '';
            if ($category === '' || $category === 'default' || $category === 'ippf_specific') {
                continue;
            }
            $categories[$category] = $category;
        }
        self::$categories = $categories;
        OEGlobalsBag::getInstance()->set('ISSUE_TYPE_CATEGORIES', $categories);

        return $categories;
    }

    /**
     * @return array<int, string>
     */
    public static function issueTypeStyles(): array
    {
        if (self::$styles !== null) {
            return self::$styles;
        }

        $styles = [
            0 => xl('Standard'),
            1 => xl('Simplified'),
            2 => xl('Football Injury'),
            3 => xl('IPPF Abortion'),
            4 => xl('IPPF Contraception'),
        ];
        self::$styles = $styles;
        OEGlobalsBag::getInstance()->set('ISSUE_TYPE_STYLES', $styles);

        return $styles;
    }

    public static function currentCategory(): string
    {
        return OEGlobalsBag::getInstance()->getBoolean('ippf_specific')
            ? 'ippf_specific'
            : 'default';
    }

    /**
     * @return array<string, array{string, string, string, mixed, mixed, mixed}>
     */
    public static function issueTypes(): array
    {
        if (self::$types !== null) {
            return self::$types;
        }

        $types = [];
        $rows = QueryUtils::fetchRecords(
            "SELECT * FROM `issue_types` WHERE active = 1 AND `category`=? ORDER BY `ordering`",
            [self::currentCategory()]
        );
        foreach ($rows as $row) {
            $type = is_string($row['type'] ?? null) ? $row['type'] : '';
            if ($type === '') {
                continue;
            }
            $pluralStr = is_string($row['plural'] ?? null) ? $row['plural'] : '';
            $singularStr = is_string($row['singular'] ?? null) ? $row['singular'] : '';
            $abbrStr = is_string($row['abbreviation'] ?? null) ? $row['abbreviation'] : '';
            $types[$type] = [
                xl_list_label($pluralStr),
                xl_list_label($singularStr),
                xl_list_label($abbrStr),
                $row['style'] ?? null,
                $row['force_show'] ?? null,
                $row['aco_spec'] ?? null,
            ];
        }
        self::$types = $types;
        OEGlobalsBag::getInstance()->set('ISSUE_TYPES', $types);

        return $types;
    }

    /**
     * @return array<int, string>
     */
    public static function issueClassifications(): array
    {
        if (self::$classifications !== null) {
            return self::$classifications;
        }

        $classifications = [
            0 => xl('Unknown or N/A'),
            1 => xl('Trauma'),
            2 => xl('Overuse'),
        ];
        self::$classifications = $classifications;
        OEGlobalsBag::getInstance()->set('ISSUE_CLASSIFICATIONS', $classifications);

        return $classifications;
    }
}
