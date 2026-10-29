<?php

/**
 * PatientFinderPageService
 *
 * Presentation helper for the Patient Finder page (dynamic_finder.php). It turns
 * the shared column registry into the markup and DataTables configuration the
 * page template needs: the column headers, the per-column search inputs, the
 * `columns` definition (sName per column, with the Name column rendered as a
 * link), the initial `order`, and the column count. Because it consumes the same
 * registry the server-side endpoint uses, module-contributed columns appear on
 * the page automatically.
 *
 * The translation/escaping helpers are injected (defaulting to core's xl/xla/
 * text), so the view building is unit-testable without loading core.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

final class PatientFinderPageService
{
    /** @var callable(string): string */
    private $text;

    /** @var callable(string): string */
    private $xl;

    /** @var callable(string): string */
    private $xla;

    /**
     * @param callable(string): string|null $text Escape text for HTML output (defaults to text()).
     * @param callable(string): string|null $xl   Translate a string (defaults to xl()).
     * @param callable(string): string|null $xla  Translate + escape for an attribute (defaults to xla()).
     */
    public function __construct(?callable $text = null, ?callable $xl = null, ?callable $xla = null)
    {
        $this->text = $text ?? static fn(string $s): string => text($s);
        $this->xl = $xl ?? static fn(string $s): string => xl($s);
        $this->xla = $xla ?? static fn(string $s): string => xla($s);
    }

    /**
     * Build the page's column view from the registry.
     *
     * @return array{header: string, header0: string, coljson: string, orderjson: string, colcount: int}
     *         header     - the <th> cells; header0 - the per-column search inputs;
     *         coljson    - the DataTables "columns" array body; orderjson - the "order" array body;
     *         colcount   - the number of columns.
     */
    public function buildColumnView(PatientFinderColumnRegistry $registry): array
    {
        $header = '';
        $header0 = '';
        $coljson = '';
        $orderjson = '';
        $index = 0;

        foreach ($registry->all() as $column) {
            $isName = $column->key === PatientFinderQueryBuilder::NAME_KEY;
            // The name column's placeholder reads "Search by Name" rather than
            // "Search by Full Name".
            $placeholderLabel = $isName ? ($this->xl)('Name') : $column->title;

            $header .= '   <th>' . ($this->text)($column->title) . "</th>\n";
            // NB: $placeholderLabel is a translated list label concatenated into
            // the attribute, matching the legacy finder; it is not re-escaped.
            $header0 .= "   <td ><input type='text' size='20' value='' class='form-control search_init' "
                . "placeholder='" . ($this->xla)('Search by') . ' ' . $placeholderLabel . "'/></td>\n";

            if ($coljson !== '') {
                $coljson .= ', ';
            }
            $coljson .= '{"sName": "' . addcslashes($column->key, "\t\r\n\"\\") . '"';
            if ($isName) {
                $coljson .= ', "mRender": wrapInLink';
            }
            $coljson .= '}';

            if ($orderjson !== '') {
                $orderjson .= ', ';
            }
            $orderjson .= '["' . $index . '", "' . addcslashes($column->defaultSortDir, "\t\r\n\"\\") . '"]';

            ++$index;
        }

        return [
            'header' => $header,
            'header0' => $header0,
            'coljson' => $coljson,
            'orderjson' => $orderjson,
            'colcount' => $index,
        ];
    }
}
