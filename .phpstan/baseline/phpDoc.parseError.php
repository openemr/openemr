<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var has invalid value \\(OpenEMR/Core/ModulesApplication
Defined in globals\\.php\\)\\: Unexpected token "/Core/ModulesApplication", expected TOKEN_HORIZONTAL_WS at offset 16 on line 1$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return has invalid value \\(sha1\\(or sha3\\-512\\)\\|empty string\\)\\: Unexpected token "\\(", expected TOKEN_HORIZONTAL_WS at offset 181 on line 7$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/DbRow/Signable.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(force \'1\' to ignore specified wait interval, \'0\' to honor wait interval

The same parameters can be accessed via Ajax using the \\$_POST variables
\'site\', \'background_service\', and \'background_force\', respectively\\.

For both calling methods, this script guarantees that each active
background service function\\: \\(1\\) will not be called again before it has completed,
and \\(2\\) will not be called any more frequently than at the specified interval
\\(unless the force execution flag is used\\)\\.  A service function that is already running
will not be called a second time even if the force execution flag is used\\.

Notes for the default background behavior\\:
1\\. If the Ajax method is used, services will only be checked while
Ajax requests are being received, which is currently only when users are
logged in\\.
2\\. All services are checked and called sequentially in the order specified
by the sort_order field in the background_services table\\. Service calls that are "slow"
should be given a higher sort_order value\\.
3\\. The actual interval between two calls to a given background service may be
as long as the time to complete that service plus the interval between
n\\+1 calls to this script where n is the number of other services preceding it
in the array, even if the specified minimum interval is shorter, so plan
accordingly\\. Example\\: with a 5 min cron interval, the 4th service on the list
may not be started again for up to 20 minutes after it has completed if
services 1, 2, and 3 take more than 15, 10, and 5 minutes to complete,
respectively\\.

Returns a count of due messages for current user\\.\\)\\: Unexpected token "\'1\'", expected variable at offset 388 on line 9$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/execute_background_services.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(service to specify a specific service, \'all\' used if omitted\\)\\: Unexpected token "to", expected variable at offset 319 on line 8$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/execute_background_services.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(site to specify a specific site, \'default\' used if omitted\\)\\: Unexpected token "to", expected variable at offset 247 on line 7$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/execute_background_services.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(\\(optional\\) \\(int\\) quality for \'jpeg\' type\\)\\: Unexpected token "\\(", expected variable at offset 221 on line 5$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/Thumbnail.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(\\(resource\\) file resource from create_thumbnail\\(\\)\\)\\: Unexpected token "file", expected variable at offset 96 on line 3$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/Thumbnail.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(\\(string\\) file name \\(pull path with wanted name\\)\\)\\: Unexpected token "file", expected variable at offset 157 on line 4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/Thumbnail.class.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(array   the array of archived file names and retained file names\\)\\: Unexpected token "the", expected variable at offset 254 on line 10$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(string  the csv type file or claim\\)\\: Unexpected token "the", expected variable at offset 209 on line 9$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(string  the file type\\)\\: Unexpected token "the", expected variable at offset 177 on line 8$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(string                     patient control\\-\\- pid\\-encounter, encounter, or pid\\)\\: Unexpected token "patient", expected variable at offset 384 on line 10$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(string                     search type encounter, pid, or clm01\\)\\: Unexpected token "search", expected variable at offset 539 on line 12$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(string  path to x12 file\\)\\: Unexpected token "path", expected variable at offset 332 on line 12$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(bool     false\\: \\$claimid is pt transaction, true\\: \\$claimid is trace from 835 or 999\\)\\: Unexpected token "false", expected variable at offset 371 on line 8$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(string \\- if \'first\' return first facility ordered by id\\)\\: Unexpected token "\\-", expected variable at offset 128 on line 7$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(array unused in this plugin, this plugin uses \\{@link Smarty\\:\\:\\$_config\\},
             \\{@link Smarty\\:\\:\\$_tpl_vars\\} and \\{@link Smarty\\:\\:\\$_smarty_debug_info\\}\\)\\: Unexpected token "unused", expected variable at offset 227 on line 8$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.assign_debug_info.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(array An array of filter callbacks\\.\\)\\: Unexpected token "An", expected variable at offset 124 on line 7$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(array An array of filter callbacks\\.\\)\\: Unexpected token "An", expected variable at offset 136 on line 7$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(string \\(optional\\) \\$action the user requested action \\(if not provided will use router\\-\\>GetRoute\\(\\)\\)\\)\\: Unexpected token "\\(", expected variable at offset 331 on line 9$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Dispatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(array debug_backtrace\\. For example\\: debug_backtrace\\(\\) \\-or\\- \\$exception\\-\\>getTrace\\(\\)\\)\\: Unexpected token "debug_backtrace", expected variable at offset 246 on line 8$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Util/ExceptionFormatter.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @property has invalid value \\(string\\|null For backwards compatibility\\)\\: Unexpected token "For", expected variable at offset 33 on line 2$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/Qdm/QDMBaseType.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(array Output from gacl_api\\-\\>sorted_groups\\(\\$group_type\\)\\)\\: Unexpected token "Output", expected variable at offset 311 on line 9$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(ISearchField\\> \\$openEMRSearchParameters \\<string, OpenEMR search fields\\)\\: Unexpected token "\\>", expected variable at offset 98 on line 3$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return has invalid value \\(ClientAdminController\\|__anonymous@16704\\)\\: Unexpected token "@16704", expected TOKEN_HORIZONTAL_WS at offset 52 on line 2$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/FHIR/SMART/ClientAdminControllerTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
