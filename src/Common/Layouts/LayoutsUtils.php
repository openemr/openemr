<?php

/**
 * LayoutsUtils class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2023 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Layouts;

class LayoutsUtils
{
    public static function getListItemTitle($list, $option)
    {
        $row = sqlQuery("SELECT `title` FROM `list_options` WHERE `list_id` = ? AND `option_id` = ? AND activity = 1", [$list, $option]);
        if (empty($row['title'])) {
            return $option;
        }
        return xl_list_label($row['title']);
    }

    /**
     * Test if modifier($test) is in array of options for data type.
     *
     * @param array $options json ["G","P","T"], ["G"] or could be legacy string with form "GPT", "G", "012"
     * @param string $test
     * @return bool
     */
    public static function isOption($options, string $test): bool
    {
        if (empty($options) || !isset($test) || $options == "null") {
            return false; // why bother?
        }
        if (!str_contains((string) $options, ',')) { // not json array of modifiers.
            // could be string of char's or single element of json ["RO"] or "TP" or "P" e.t.c.
            json_decode((string) $options, true); // test if options json. json_last_error() will return JSON_ERROR_SYNTAX if not.
            // if of form ["RO"] (single modifier) means not legacy so continue on.
            if (is_string($options) && (json_last_error() !== JSON_ERROR_NONE)) { // nope, it's string.
                $t = str_split(trim($options)); // very good chance it's legacy modifier string.
                $options = json_encode($t); // make it json array to convert from legacy to new modifier json schema.
            }
        }

        $options = json_decode((string) $options, true); // all should now be json

        return is_array($options) && in_array($test, $options, true); // finally the truth!
    }

    /**
     * Static Text (data_type 31) uses a textarea for Description; other types use a text input.
     */
    public static function descriptionUsesTextarea(mixed $dataType): bool
    {
        return $dataType === 31 || $dataType === '31';
    }

    /**
     * Whether the layout editor should emit the Description translation column.
     *
     * Independent of data type so Static Text rows keep the same cell as the header.
     */
    public static function includeDescriptionTranslation(bool $translateLayout, mixed $languageChoice): bool
    {
        if (!$translateLayout) {
            return false;
        }
        if (is_int($languageChoice) || is_float($languageChoice)) {
            return $languageChoice > 1;
        }
        if (is_string($languageChoice) && is_numeric($languageChoice)) {
            return (float) $languageChoice > 1;
        }

        return false;
    }

    /**
     * Description control plus the matching translation cell for the layout editor.
     *
     * Static Text uses a textarea; other types use a text input. The translation
     * cell is independent of data type so it stays aligned with the header.
     *
     * The caller supplies the translated label rather than this class calling
     * xl_layout_label(): that helper needs the globals bag and the translation
     * tables, which would tie this method to a booted application.
     */
    public static function descriptionEditorCellsHtml(
        mixed $dataType,
        mixed $description,
        int $lineNo,
        bool $includeTranslation,
        string $translatedDescription = ''
    ): string {
        $descStr = is_string($description) ? $description : '';
        $html = "  <td class='text-center optcell'>";
        if (self::descriptionUsesTextarea($dataType)) {
            $html .= "<textarea name='fld[" . attr((string) $lineNo) . "][desc]' rows='3' cols='35' class='form-control form-control-sm optin'>" .
                text($descStr) . "</textarea>";
        } else {
            $html .= "<input type='text' name='fld[" . attr((string) $lineNo) . "][desc]' value='" .
                attr($descStr) . "' size='20' class='form-control form-control-sm optin' />";
        }
        $html .= "</td>\n";
        if ($includeTranslation) {
            $html .= "<td class='text-center translation'>" . text($translatedDescription) . "</td>\n";
        }

        return $html;
    }
}
