<?php declare(strict_types = 1);

// total 20 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method pnHTML\\:\\:EndPage\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Method pnHTML\\:\\:FormEnd\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Method pnHTML\\:\\:FormHidden\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Method pnHTML\\:\\:FormSelectMultiple\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Method pnHTML\\:\\:FormStart\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Method pnHTML\\:\\:FormSubmit\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Method pnHTML\\:\\:Linebreak\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Method pnHTML\\:\\:StartPage\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Method pnHTML\\:\\:Text\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Compiler_Legacy\\:\\:_compile_tag\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Compiler_Legacy\\:\\:_pop_tag\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_run_insert_handler\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.run_insert_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_checkboxes\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_checkboxes.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_radios\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_radios.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_popup_init\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.popup_init.php',
];
$ignoreErrors[] = [
    'message' => '#^Method sms\\:\\:_send_sock\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/sms_tmb4.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Savant3_Filter_trimwhitespace\\:\\:replace\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3/resources/Savant3_Filter_trimwhitespace.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:_check_count\\(\\) should return special but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:encoding\\(\\) should return nothing but return statement is missing\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
