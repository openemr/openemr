<?php

/**
 * MedEx API - Backward Compatibility Facade
 *
 * This file maintains backward compatibility by importing all extracted service classes.
 * The monolithic API.php has been refactored into focused service classes.
 *
 * New structure:
 * - Client/HttpClient.php - HTTP communication (replaces CurlRequest)
 * - Services/BaseService.php - Base class for all services
 * - Services/PracticeService.php - Practice synchronization
 * - Services/CampaignService.php - Campaign management
 * - Services/EventsService.php - Event generation (TODO: complete extraction)
 * - Services/CallbackService.php - Message response handling
 * - Services/LoggingService.php - Debug logging
 * - Services/DisplayService.php - UI rendering (TODO: complete extraction)
 * - Services/SetupService.php - Registration wizard
 * - MedExClass.php - Main coordinator class
 * - Exceptions/InvalidDataException.php - Custom exception
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018 MedEx <support@MedExBank.com>
 * @license   Proprietary - All Rights Reserved
 */

namespace MedExApi;

// Load OEGlobalsBag polyfill if the class is not available via the autoloader
// (occurs when OpenEMR's src/Core/ is not yet deployed or Symfony is missing)
if (!class_exists('OpenEMR\\Core\\OEGlobalsBag', false)) {
    // Try autoloader first
    class_exists('OpenEMR\\Core\\OEGlobalsBag');
    // If still not found, load polyfill
    if (!class_exists('OpenEMR\\Core\\OEGlobalsBag', false)) {
        require_once(__DIR__ . '/OEGlobalsBag_polyfill.php');
    }
}

// Import all extracted classes
require_once(__DIR__ . '/Client/HttpClient.php');
require_once(__DIR__ . '/Services/BaseService.php');
require_once(__DIR__ . '/Services/PracticeService.php');
require_once(__DIR__ . '/Services/CampaignService.php');
require_once(__DIR__ . '/Services/EventsService.php');
require_once(__DIR__ . '/Services/CallbackService.php');
require_once(__DIR__ . '/Services/LoggingService.php');
require_once(__DIR__ . '/Services/DisplayService.php');
require_once(__DIR__ . '/Services/SetupService.php');
require_once(__DIR__ . '/MedExClass.php');
require_once(__DIR__ . '/Exceptions/InvalidDataException.php');

// Create backward-compatible aliases (old class names -> new classes)
class_alias('MedExApi\Client\HttpClient', 'MedExApi\CurlRequest');
class_alias('MedExApi\Services\BaseService', 'MedExApi\Base');
class_alias('MedExApi\Services\PracticeService', 'MedExApi\Practice');
class_alias('MedExApi\Services\CampaignService', 'MedExApi\Campaign');
class_alias('MedExApi\Services\EventsService', 'MedExApi\Events');
class_alias('MedExApi\Services\CallbackService', 'MedExApi\Callback');
class_alias('MedExApi\Services\LoggingService', 'MedExApi\Logging');
class_alias('MedExApi\Services\DisplayService', 'MedExApi\Display');
class_alias('MedExApi\Services\SetupService', 'MedExApi\Setup');

// The main MedEx class is already in the correct namespace
// No alias needed - it's MedExApi\MedEx in both old and new code
