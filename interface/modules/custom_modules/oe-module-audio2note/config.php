<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Module Metadata
$moduleMetaData['name'] = 'OpenEMR Audio2Note Integration';
$moduleMetaData['description'] = 'Integrates OpenEMR with an external audio processing service for transcription and SOAP note population.';
$moduleMetaData['version'] = '0.1.0'; // Initial version
$moduleMetaData['dependencies'] = array('openemr' => '7.0.3'); // Specify OpenEMR version dependency
$moduleMetaData['category'] = 'Clinical'; // Or another appropriate category

// Configuration settings for the Audio to Note integration.
// These settings are used by the module to interact with external services and define behavior.
$openemrAudio2NoteConfig = [
    // URL for the backend audio processing API webhook.
    'transcription_service_api_url' => 'https://backend.audio2note.org/webhook/transcribe',
    // OAuth2 Client ID for OpenEMR API. To be configured during setup.
    'openemr_api_client_id' => '',
    // OAuth2 Client Secret for OpenEMR API. To be configured during setup.
    'openemr_api_client_secret' => '',
    // Default parameters for transcription requests.
    'transcription_params' => [
        'min_speakers' => 1,
        'max_speakers' => 2,
        'output_format' => 'json', // JSON output is required for parsing.
    ],
    // Mapping of transcription service output fields to OpenEMR SOAP note fields.
    'soap_note_mapping' => [
        'transcription' => 'subjective',
        'hnp' => 'objective',
        'billing' => 'assessment',
    ],
    // Default encounter details if a new encounter needs to be created.
    // Values should be verified against the specific OpenEMR instance's lists.
    'default_encounter' => [
        'pc_catid' => '5',    // Example: 'Office Visit'
        'class_code' => 'AMB', // Example: 'ambulatory'
    ],
    // Secret for securing the status callback endpoint.
    // IMPORTANT: This should be replaced with a strong, unique secret in a production environment.
    'callback_secret' => 'secret101',
];

// The $openemrAudio2NoteConfig array is typically accessed directly where needed,
// rather than being declared global.

?>
