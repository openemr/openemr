<?php

/**
 * Factory for creating lab-specific HL7 order generators.
 *
 * Resolves the correct Hl7OrderGeneratorInterface implementation based on the
 * lab type string, loading the necessary legacy procedural include files.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Josh Baiad <josh@joshbaiad.com>
 * @copyright Copyright (c) 2026 Josh Baiad <josh@joshbaiad.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Orders;

class Hl7OrderGeneratorFactory
{
    /** @var array<string, class-string<Hl7OrderGeneratorInterface>> */
    private const LAB_TYPE_MAP = [
        'labcorp' => LabCorpHl7OrderGenerator::class,
        'quest'   => QuestHl7OrderGenerator::class,
        'ammon'   => UniversalHl7OrderGenerator::class,
        'clarity' => UniversalHl7OrderGenerator::class,
    ];

    /**
     * Relative paths (from the interface/ directory) to require_once per lab type.
     *
     * @var array<string, list<string>>
     */
    private const LEGACY_INCLUDES = [
        'labcorp' => [
            'procedure_tools/labcorp/ereq_form.php',
            'procedure_tools/labcorp/gen_hl7_order.inc.php',
        ],
        'quest' => [
            'procedure_tools/quest/gen_hl7_order.inc.php',
        ],
        'ammon' => [
            'procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
        ],
        'clarity' => [
            'procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
        ],
        'default' => [
            'procedure_tools/ereqs/ereq_universal_form.php',
            'orders/gen_hl7_order.inc.php',
        ],
    ];

    /**
     * Create the appropriate HL7 order generator for the given lab type.
     *
     * @param string $labType       Lab identifier (e.g., 'labcorp', 'quest', 'ammon', 'clarity')
     * @param string $interfaceDir  Absolute path to the interface/ directory
     * @return Hl7OrderGeneratorInterface
     */
    public static function create(string $labType, string $interfaceDir): Hl7OrderGeneratorInterface
    {
        $includeKey = array_key_exists($labType, self::LEGACY_INCLUDES) ? $labType : 'default';
        foreach (self::LEGACY_INCLUDES[$includeKey] as $relativePath) {
            require_once $interfaceDir . DIRECTORY_SEPARATOR . $relativePath;
        }

        $class = self::LAB_TYPE_MAP[$labType] ?? DefaultHl7OrderGenerator::class;
        return new $class();
    }

    /**
     * Return the list of explicitly supported lab type keys (excluding 'default').
     *
     * @return list<string>
     */
    public static function supportedLabTypes(): array
    {
        return array_keys(self::LAB_TYPE_MAP);
    }
}
