<?php

/**
 * XpathsConstantsEditGlobals class
 *
 * XPath selector constants for the edit_globals.php configuration page.
 * Centralizes all XPath expressions used in E2E tests for easier maintenance.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Xpaths;

/**
 * XPath constants for edit_globals.php E2E tests.
 *
 * Provides reusable XPath selectors for:
 * - Main page elements (iframe, form, buttons)
 * - Tab navigation elements
 * - Search functionality elements
 */
class XpathsConstantsEditGlobals
{
    // Main page elements

    /**
     * XPath for the iframe containing the edit_globals.php page.
     */
    public const GLOBALS_IFRAME = '//iframe[contains(@src, "edit_globals.php")]';

    /**
     * XPath for the main globals configuration form.
     */
    public const GLOBALS_FORM = '//form[@name="theform"]';

    /**
     * XPath for the Save button.
     */
    public const SAVE_BUTTON = '//button[@name="form_save"]';

    /**
     * XPath for the search input field.
     */
    public const SEARCH_INPUT = '//input[@name="srch_desc"]';

    /**
     * XPath for the search submit button.
     */
    public const SEARCH_BUTTON = '//button[@name="form_search"]';

    // Tab navigation

    /**
     * XPath for the Locale configuration tab.
     */
    public const TAB_LOCALE = '//ul[@id="oe-nav-ul"]//a[contains(text(), "Locale")]';

    /**
     * XPath for the Appearance configuration tab.
     */
    public const TAB_APPEARANCE = '//ul[@id="oe-nav-ul"]//a[contains(text(), "Appearance")]';

    /**
     * XPath for the Security configuration tab.
     */
    public const TAB_SECURITY = '//ul[@id="oe-nav-ul"]//a[contains(text(), "Security")]';

    /**
     * XPath for the Connectors configuration tab.
     */
    public const TAB_CONNECTORS = '//ul[@id="oe-nav-ul"]//a[contains(text(), "Connectors")]';

    /**
     * XPath for the Features configuration tab.
     */
    public const TAB_FEATURES = '//ul[@id="oe-nav-ul"]//a[contains(text(), "Features")]';

    // Search results

    /**
     * XPath for search result highlight markers.
     */
    public const SEARCH_HIGHLIGHT = '//mark';

    /**
     * XPath for rows that match search criteria (have 'srch' class).
     */
    public const SEARCH_RESULT_ROW = '//div[contains(@class, "srch")]';
}
