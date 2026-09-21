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

use InvalidArgumentException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

/**
 * Lazy loader and extension point for the `issue_types` lookup tables that
 * legacy code reads as top-level globals ($ISSUE_TYPES, $ISSUE_TYPE_CATEGORIES,
 * $ISSUE_TYPE_STYLES, $ISSUE_CLASSIFICATIONS). Each getter writes its result
 * into OEGlobalsBag on every call so `global $ISSUE_TYPES;` and
 * `OEGlobalsBag::get('ISSUE_TYPES')` readers continue to work; kept for
 * backwards compatibility.
 *
 * Issue types and categories are data-driven: extend them by adding rows to the
 * `issue_types` table. Styles and classifications are code-driven — their
 * meaning lives in PHP (form rendering, reporting) rather than the database — so
 * modules extend them in code through the registerStyle() and
 * registerClassification() methods, typically from a module's bootstrap before
 * any page reads the arrays. The class stays final; extension is by registration
 * (composition), not inheritance.
 */
final class IssueTypeRegistry
{
    /** Style ids owned by core; registering over one requires $overwrite. */
    private const DEFAULT_STYLE_IDS = [0, 1, 2, 3, 4];

    /** Classification ids owned by core; registering over one requires $overwrite. */
    private const DEFAULT_CLASSIFICATION_IDS = [0, 1, 2];

    /** @var array<string, string>|null */
    private static ?array $categories = null;

    /** @var array<string, array{string, string, string, mixed, mixed, mixed}>|null */
    private static ?array $types = null;

    /** @var array<int, string> Module-registered styles, merged over the core defaults. */
    private static array $registeredStyles = [];

    /** @var array<int, string> Module-registered classifications, merged over the core defaults. */
    private static array $registeredClassifications = [];

    /**
     * Register an additional issue-type style so it appears wherever core builds
     * the style list (e.g. the issue-types admin editor). The style's behaviour
     * (how the issue form renders for it) is the caller's responsibility.
     *
     * @param int    $id        The style id stored in `issue_types`.`style`.
     * @param string $label     Human-readable, already-translated label (e.g. xl('Problem')).
     * @param bool   $overwrite Allow replacing a style id that is already defined.
     *
     * @throws InvalidArgumentException on an empty label, or on a collision when $overwrite is false.
     */
    public static function registerStyle(int $id, string $label, bool $overwrite = false): void
    {
        self::register('style', self::$registeredStyles, self::DEFAULT_STYLE_IDS, $id, $label, $overwrite);
    }

    /**
     * Register an additional issue classification. See registerStyle() for the
     * contract; classifications feed the injury classification dropdowns.
     *
     * @throws InvalidArgumentException on an empty label, or on a collision when $overwrite is false.
     */
    public static function registerClassification(int $id, string $label, bool $overwrite = false): void
    {
        self::register('classification', self::$registeredClassifications, self::DEFAULT_CLASSIFICATION_IDS, $id, $label, $overwrite);
    }

    /**
     * Shared validation/collision handling for the code-driven registries.
     *
     * @param array<int, string> $registered Registration store, passed by reference.
     * @param list<int>          $defaultIds Ids owned by core.
     */
    private static function register(string $kind, array &$registered, array $defaultIds, int $id, string $label, bool $overwrite): void
    {
        if (trim($label) === '') {
            throw new InvalidArgumentException("Issue $kind $id must have a non-empty label.");
        }
        if (!$overwrite && (in_array($id, $defaultIds, true) || array_key_exists($id, $registered))) {
            throw new InvalidArgumentException(
                "Issue $kind $id is already defined; pass \$overwrite = true to replace it."
            );
        }
        $registered[$id] = $label;
    }

    /**
     * Reset all memoized and registered state. Intended for test isolation.
     */
    public static function reset(): void
    {
        self::$categories = null;
        self::$types = null;
        self::$registeredStyles = [];
        self::$registeredClassifications = [];
    }

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
        $styles = [
            0 => xl('Standard'),
            1 => xl('Simplified'),
            2 => xl('Football Injury'),
            3 => xl('IPPF Abortion'),
            4 => xl('IPPF Contraception'),
        ];
        foreach (self::$registeredStyles as $id => $label) {
            $styles[$id] = $label;
        }
        ksort($styles);
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
        $classifications = [
            0 => xl('Unknown or N/A'),
            1 => xl('Trauma'),
            2 => xl('Overuse'),
        ];
        foreach (self::$registeredClassifications as $id => $label) {
            $classifications[$id] = $label;
        }
        ksort($classifications);
        OEGlobalsBag::getInstance()->set('ISSUE_CLASSIFICATIONS', $classifications);

        return $classifications;
    }
}
