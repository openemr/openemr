<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Modules\\\\ClaimRevConnector\\\\CustomSkeletonFHIRResourceService not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Modules\\\\Dorn\\\\CustomSkeletonFHIRResourceService not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\Dorn\\\\models\\\\OrderStatusViewModel\\:\\:\\$createdDateTimeUtc has unknown class OpenEMR\\\\Modules\\\\Dorn\\\\models\\\\DateTime as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/models/OrderStatusViewModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Caught class OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\Exception not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class QuestResultClient not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Caught class MpdfException not found\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method NWeekdayOfMonth\\(\\) on an unknown class MedExApi\\\\Date_Calc\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class MedExApi\\\\DateTime not found\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class TwigContainer not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/immunization_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class OpenEMR\\\\PatientPortal\\\\Chat\\\\ChatController not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/messaging/secure_chat.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class specify not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method createWriter\\(\\) on an unknown class PHPExcel_IOFactory\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class PHPExcel not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Class IRSSFeedItem not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class RSS_Writer not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$previous of method OpenEMR\\\\Common\\\\Auth\\\\Exception\\\\OneTimeAuthException\\:\\:__construct\\(\\) has invalid type OpenEMR\\\\Common\\\\Auth\\\\Exception\\\\Throwable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/Exception/OneTimeAuthException.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$previous of method OpenEMR\\\\Common\\\\Auth\\\\Exception\\\\OneTimeAuthExpiredException\\:\\:__construct\\(\\) has invalid type OpenEMR\\\\Common\\\\Auth\\\\Exception\\\\Throwable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/Exception/OneTimeAuthExpiredException.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method getInstance\\(\\) on an unknown class libphonenumber\\\\PhoneNumberUtil\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Utils/ValidationUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Caught class libphonenumber\\\\NumberParseException not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Utils/ValidationUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant E164 on an unknown class libphonenumber\\\\PhoneNumberFormat\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ValueObjects/PhoneNumber.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant INTERNATIONAL on an unknown class libphonenumber\\\\PhoneNumberFormat\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ValueObjects/PhoneNumber.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant NATIONAL on an unknown class libphonenumber\\\\PhoneNumberFormat\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ValueObjects/PhoneNumber.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant RFC3966 on an unknown class libphonenumber\\\\PhoneNumberFormat\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ValueObjects/PhoneNumber.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method getInstance\\(\\) on an unknown class libphonenumber\\\\PhoneNumberUtil\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/ValueObjects/PhoneNumber.php',
];
$ignoreErrors[] = [
    'message' => '#^Caught class libphonenumber\\\\NumberParseException not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ValueObjects/PhoneNumber.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$phoneNumber of method OpenEMR\\\\Common\\\\ValueObjects\\\\PhoneNumber\\:\\:__construct\\(\\) has invalid type libphonenumber\\\\PhoneNumber\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ValueObjects/PhoneNumber.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\ValueObjects\\\\PhoneNumber\\:\\:\\$phoneNumber has unknown class libphonenumber\\\\PhoneNumber as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ValueObjects/PhoneNumber.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\ValueObjects\\\\PhoneNumber\\:\\:\\$util has unknown class libphonenumber\\\\PhoneNumberUtil as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ValueObjects/PhoneNumber.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class OpenEMR\\\\Gacl\\\\Hashed_Cache_Lite not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
