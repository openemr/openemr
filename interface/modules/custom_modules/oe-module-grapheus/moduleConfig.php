<?php

/**
 * Module Manager "Config" button: Grapheus has no global settings to change here;
 * each clinician connects their own account from the Grapheus tab in an encounter.
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$module_config = 1;
echo '<p>' . text(xl('Open any encounter and choose the Grapheus tab to connect your Grapheus account and start recording.')) . '</p>';
echo '<p><a href="https://scribe.exetazohealth.com" target="_blank" rel="noopener">scribe.exetazohealth.com</a></p>';
