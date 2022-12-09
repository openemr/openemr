<?php

namespace OpenEMR\OemrAd;

class Globals {
	
	function __construct(){
	}

	/*Global Fields*/
	public static function setupGlobalField(&$GLOBALS_METADATA, &$USER_SPECIFIC_TABS, &$USER_SPECIFIC_GLOBALS) {
		self::ZoomIntegration($GLOBALS_METADATA, $USER_SPECIFIC_TABS, $USER_SPECIFIC_GLOBALS);
		self::ShortenLink($GLOBALS_METADATA, $USER_SPECIFIC_TABS, $USER_SPECIFIC_GLOBALS);
		self::Utility($GLOBALS_METADATA, $USER_SPECIFIC_TABS, $USER_SPECIFIC_GLOBALS);
		self::Smslib($GLOBALS_METADATA, $USER_SPECIFIC_TABS, $USER_SPECIFIC_GLOBALS);
		self::Twiliolib($GLOBALS_METADATA, $USER_SPECIFIC_TABS, $USER_SPECIFIC_GLOBALS);
		self::ApiLib($GLOBALS_METADATA, $USER_SPECIFIC_TABS, $USER_SPECIFIC_GLOBALS);
		self::CaseLib($GLOBALS_METADATA, $USER_SPECIFIC_TABS, $USER_SPECIFIC_GLOBALS);
		self::CoverageCheck($GLOBALS_METADATA, $USER_SPECIFIC_TABS, $USER_SPECIFIC_GLOBALS);
		self::EmailMessage($GLOBALS_METADATA, $USER_SPECIFIC_TABS, $USER_SPECIFIC_GLOBALS);
		self::FaxMessage($GLOBALS_METADATA, $USER_SPECIFIC_TABS, $USER_SPECIFIC_GLOBALS);
		self::PostalLetter($GLOBALS_METADATA, $USER_SPECIFIC_TABS, $USER_SPECIFIC_GLOBALS);
	}

	public static function ZoomIntegration(&$GLOBALS_METADATA, &$USER_SPECIFIC_TABS, &$USER_SPECIFIC_GLOBALS) {
		/*Configure For Calnder*/

		/*$GLOBALS_METADATA['Zoom Integration']['zoom_user_id'] = array(
			xl('UserId Or Email Id'),
            'text',                           // data type
            '',                      // default
            xl('To performance zoom api operations.')
		);

		$GLOBALS_METADATA['Zoom Integration']['zoom_api_key'] = array(
			xl('API Key'),
            'text',                           // data type
            '',                      // default
            xl('To performance zoom api operations.')
		);

		$GLOBALS_METADATA['Zoom Integration']['zoom_api_secret'] = array(
			xl('API Secret'),
            'text',                           // data type
            '',                      // default
            xl('To performance zoom api operations.')
		);*/

		$GLOBALS_METADATA['Zoom Integration']['zoom_access_token'] = array(
			xl('API Access Token'),
            'text',                           // data type
            '',                      // default
            xl('To performance zoom api operations.')
		);

		$GLOBALS_METADATA['Zoom Integration']['zoom_appt_category'] = array(
			xl('Appoiment Category For Zoom Meeting'),
            'textarea',                           // data type
            '',                      // default
            xl('Create Zoom meeting for mentioned category')
		);

		$GLOBALS_METADATA['Zoom Integration']['zoom_appt_facility'] = array(
			xl('Appoiment Facility For Zoom Meeting'),
            'textarea',                           // data type
            '',                      // default
            xl('Create Zoom meeting for mentioned facility')
		);

		$GLOBALS_METADATA['Zoom Integration']['zoom_notify_event_id'] = array(
			xl('Event Id'),
            'textarea',                           // data type
            '',                      // default
            xl('Event Id')
		);

		$GLOBALS_METADATA['Zoom Integration']['zoom_notify_config_id'] = array(
			xl('Config Id'),
            'textarea',                           // data type
            '',                      // default
            xl('Config Id')
		);
	}

	public static function ShortenLink(&$GLOBALS_METADATA, &$USER_SPECIFIC_TABS, &$USER_SPECIFIC_GLOBALS) {

		/*Configure For Calnder*/
		$GLOBALS_METADATA['ShortenLink']['shortenlink_service'] = array(
			xl('ShortLink Service'),
            array(
                'bitly' => xl('Bitly'),
                'tinyurl' => xl('Tinyurl'),
                'shlink' => xl('Shlink'),
                'yourls' => xl('Yourls'),
            ),
            'bitly',
            xl('ShortLink Service')
		);

		$GLOBALS_METADATA['ShortenLink']['shortenlink_access_token'] = array(
			xl('Access Token'),
            'text',                           // data type
            '',                      // default
            xl('Access Token.')
		);

		$GLOBALS_METADATA['ShortenLink']['shlink_domain'] = array(
			xl('Domain (Shlink/Yourls)'),
            'text',                           // data type
            '',                      // default
            xl('Domain (Shlink/Yourls)')
		);

		$GLOBALS_METADATA['ShortenLink']['shortenlink_username'] = array(
			xl('Username (Yourls)'),
            'text',                           // data type
            '',                      // default
            xl('Username (Yourls)')
		);

		$GLOBALS_METADATA['ShortenLink']['shortenlink_password'] = array(
			xl('Password (Yourls)'),
            'text',                           // data type
            '',                      // default
            xl('Password (Yourls)')
		);
	}

	public static function Utility(&$GLOBALS_METADATA, &$USER_SPECIFIC_TABS, &$USER_SPECIFIC_GLOBALS) {

		/*Configure For Calnder*/
		$GLOBALS_METADATA['Calendar']['disable_calendar_availability_popup'] = array(
			xl('Don’t check availability'),
			'bool',                           // data type
			'0',                               // default
			xl('Don’t check availability')
		);

		$GLOBALS_METADATA['Notifications']['alert_log_recipient'] = array(
			xl('Alert log recipient'),
            'text',                           // data type
            '',                      // default
            xl('Alert log recipient')
		);

		$GLOBALS_METADATA['Notifications']['hubspot_listener_sync_config'] = array(
			xl('Hubspot Listener Sync Config'),
            'textarea',                           // data type
            '',                      // default
            xl('To sync hubspot data.')
		);

		// $GLOBALS_METADATA['Calendar']['disable_availability_popup'] = array(
		// 	xl('Don’t check Provider availability'),
		// 	'bool',                           // data type
		// 	'0',                               // default
		// 	xl('Don’t check Provider availability')
		// );

        // $GLOBALS_METADATA['Calendar']['disable_appointment_availability_popup'] = array(
        //     xl('Allow overlapping of appointments'),
        //     'bool',                           // data type
        //     '0',                               // default
        //     xl('Don’t check Provider availability')
        // );
	}

	public static function Smslib(&$GLOBALS_METADATA, &$USER_SPECIFIC_TABS, &$USER_SPECIFIC_GLOBALS) {
		/*Configure For Calnder*/
		$GLOBALS_METADATA['SMS Service']['SMS_SERVICE_TYPE'] = array(
			xl('SMS Service'),
            array(
                'nexmo' => xl('Nexmo'),
                'twilio' => xl('Twilio'),
            ),
            'nexmo',
            xl('SMS Service')
		);

		$GLOBALS_METADATA['SMS Service']['EXTRA_SMS_TEXT'] = array(
			xl('Extra SMS Text'),
            'text',                           // data type
            '',                      // default
            xl('Extra SMS Text')
		);

		$GLOBALS_METADATA['SMS Service']['EXTRA_SMS_TEXT_INTERVAL'] = array(
			xl('SMS Text Day Interval Day'),
            'text',                           // data type
            '30',                      // default
            xl('SMS Text Day Interval Day')
		);

		$GLOBALS_METADATA['Notifications']['APPT_CONFIRM_CONFIG_ID'] = array(
			xl('Special Appointment Confirmation Config Id'),
            'text',                           // data type
            '',                      // default
            xl('Special Appointment Confirmation Config Id')
		);
	}

	public static function Twiliolib(&$GLOBALS_METADATA, &$USER_SPECIFIC_TABS, &$USER_SPECIFIC_GLOBALS) {
		/*Configure For Calnder*/
		$GLOBALS_METADATA['Twilio Integration']['SMS_TWILIO_ACCOUNT_SID'] = array(
			xl('Twilio Account SID'),
            'text',                           // data type
            '',                      // default
            xl('To performance Twilio api operations.')
		);

		$GLOBALS_METADATA['Twilio Integration']['SMS_TWILIO_AUTH_TOKEN'] = array(
			xl('Twilio Auth Token Token'),
            'text',                           // data type
            '',                      // default
            xl('To performance Twilio api operations.')
		);

		$GLOBALS_METADATA['Twilio Integration']['SMS_TWILIO_DEFAULT_FROM'] = array(
			xl('Twilio From Number'),
            'text',                           // data type
            '',                      // default
            xl('To performance Twilio api operations.')
		);

		$GLOBALS_METADATA['Twilio Integration']['SMS_TWILIO_SITE_URL'] = array(
			xl('Site URL'),
            'text',                           // data type
            '',                      // default
            xl('Site URL')
		);
	}

	public static function ApiLib(&$GLOBALS_METADATA, &$USER_SPECIFIC_TABS, &$USER_SPECIFIC_GLOBALS) {
		$GLOBALS_METADATA['Apis']['oemr_api_token'] = array(
			xl('Api Key'),
            'text',                           // data type
            '',                      // default
            xl('Api key for auth')
		);

		$GLOBALS_METADATA['Apis']['oemr_xibo_appt_cat'] = array(
			xl('Xibo Appt Categories'),
            'text',                           // data type
            '',                      // default
            xl('Xibo Appt Categories')
		);
	}

	public static function CaseLib(&$GLOBALS_METADATA, &$USER_SPECIFIC_TABS, &$USER_SPECIFIC_GLOBALS) {
		$GLOBALS_METADATA['Notifications']['ct_notification_categories'] = array(
			xl('Care Team Notification Categories'),
            'textarea',                           // data type
            '',                      // default
            xl('To send care team notification')
		);
	}

	public static function EmailMessage(&$GLOBALS_METADATA, &$USER_SPECIFIC_TABS, &$USER_SPECIFIC_GLOBALS) {
		$GLOBALS_METADATA['Notifications']['IMAP_SERVER_URL'] = array(
			xl('IMAP Server URL'),
            'text',                           // data type
            '',                      // default
            xl('IMAP Server URL used for get incoming emails')
		);
		
		$GLOBALS_METADATA['Notifications']['IMAP_USER'] = array(
            xl('IMAP User for Authentication'),
            'text',                           // data type
            '',                               // default
            xl('Must be empty if IMAP authentication is not used.')
        );

        $GLOBALS_METADATA['Notifications']['IMAP_PASS'] = array(
            xl('IMAP Password for Authentication'),
            'text',                           // data type
            '',                               // default
            xl('Must be empty if IMAP authentication is not used.')
        );

        $GLOBALS_METADATA['Notifications']['IMAP_ON_MESSAGE_BOARD_PAGE_SYNC'] = array(
						xl('Sync Emails On Message Board Page'),
            array(
                'true' => 'True',
                'false' => 'False'
            ),                          // data type
            'false',                      // default
            xl('Sync email on message board page')
				);

        $GLOBALS_METADATA['Notifications']['IMAP_ON_PAGE_SYNC'] = array(
						xl('IMAP On Page Sync Run'),
            array(
                'true' => 'True',
                'false' => 'False'
            ),                          // data type
            'true',                      // default
            xl('Used to run sync email on page')
				);

				$GLOBALS_METADATA['Notifications']['IMAP_DELETE_AFTER_SYNC'] = array(
						xl('IMAP Delete Email After Sync'),
            array(
                'true' => 'Yes',
                'false' => 'No'
            ),                          // data type
            'false',                      // default
            xl('Used for delete email after sync.')
				);

				$GLOBALS_METADATA['Notifications']['SYNC_EXIST_USER_EMAIL'] = array(
            xl('Sync Emails For NonExisting Email Addresses'),
            array(
                'true' => 'True',
                'false' => 'False'
            ),                          // data type
            'false',                                  // default
            xl('Sync Emails For NonExisting Email Addresses')
        );

        $GLOBALS_METADATA['Notifications']['EMAIL_MAX_ATTACHMENT_SIZE'] = array(
           xl('Email Max Attachment Size in MB'),
            'text',                           // data type
            '10',                               // default
            xl('Email Max Attachment Size in MB')
        );

        $GLOBALS_METADATA['Notifications']['EMAIL_FROM_NAME'] = array(
           xl('Email From name'),
            'text',                           // data type
            '',                               // default
            xl('Email From name')
        );

        $GLOBALS_METADATA['PDF']['pdf_header_margin'] = array(
           xl('Header margin (mm)'),
            'text',                           // data type
            '0',                               // default
            xl('Header margin (mm)')
        );

        $GLOBALS_METADATA['PDF']['pdf_footer_margin'] = array(
           xl('Footer margin (mm)'),
            'text',                           // data type
            '0',                               // default
            xl('Header margin (mm)')
        );
	}

	public static function FaxMessage(&$GLOBALS_METADATA, &$USER_SPECIFIC_TABS, &$USER_SPECIFIC_GLOBALS) {
		$GLOBALS_METADATA['Fax']['FAX_USER'] = array(
            xl('Fax User for Authentication'),
            'text',                           // data type
            '',                               // default
            xl('Must be empty if Fax authentication is not used.')
        );

        $GLOBALS_METADATA['Fax']['FAX_PASS'] = array(
            xl('Fax Password for Authentication'),
            'text',                           // data type
            '',                               // default
            xl('Must be empty if Fax authentication is not used.')
        );

        $GLOBALS_METADATA['Fax']['FAX_SRC'] = array(
            xl('vfax DID to send fax from'),
            'text',                           // data type
            '',                               // default
            xl('The vfax DID to send fax from')
        );

        $GLOBALS_METADATA['Fax']['FAX_CHECK_STATUS_AFTER'] = array(
            xl('Check Fax Status After MIN'),
            'text',                           // data type
            '',                               // default
            xl('Check Fax Status After Min')
        );

        $GLOBALS_METADATA['Fax']['FAX_INITIAL_COST'] = array(
            xl('Initial Cost'),
            'text',                           // data type
            '',                               // default
            xl('Initial Cost')
        );

        $GLOBALS_METADATA['Fax']['FAX_ADDITIONAL_COST'] = array(
            xl('Additional Page Cost'),
            'text',                           // data type
            '',                               // default
            xl('Additional Page Cost')
        );

        $GLOBALS_METADATA['Fax']['FAX_LIMIT_COST'] = array(
            xl('Limit Cost'),
            'text',                           // data type
            '',                               // default
            xl('Limit Cost')
        );
	}

	public static function PostalLetter(&$GLOBALS_METADATA, &$USER_SPECIFIC_TABS, &$USER_SPECIFIC_GLOBALS) {
        $GLOBALS_METADATA['Postal Letter']['POSTAL_LETTER_SECRETKEY'] = array(
            xl('Postal Letter Secret Key'),
            'text',                           // data type
            '',                               // default
            xl('Must be empty if Postal Letter is not used.')
        );

        $GLOBALS_METADATA['Postal Letter']['POSTAL_LETTER_WORKMODE'] = array(
            xl('Postal Letter WorkMode'),
            array(
                'Default' => xl('Default'),
                'Production' => xl('Production'),
                'Development' => xl('Development'),
            ),
            'Default',                               // default
            xl('Postal Letter WorkMode')
        );


        $GLOBALS_METADATA['Postal Letter']['POSTAL_LETTER_SEL_REPlY_ADDRESS'] = array(
            xl('Select Reply Address'),
            'text',                           // data type
            '',                               // default
            xl('Select Reply Address')
        );

        $GLOBALS_METADATA['Postal Letter']['POSTAL_LETTER_REPlY_ADDRESS'] = array(
            xl('Reply Address'),
            'textarea',                           // data type
            '',                               // default
            xl('Reply Address')
        );

        $GLOBALS_METADATA['Postal Letter']['POSTAL_LETTER_REPlY_ADDRESS_JSON'] = array(
            xl('REPlY_ADDRESS_JSON'),
            'textarea',                           // data type
            '""',                               // default
            xl('REPlY_ADDRESS_JSON')
        );

        $GLOBALS_METADATA['Postal Letter']['POSTAL_LETTER_INITIAL_COST'] = array(
            xl('Initial Cost'),
            'text',                           // data type
            '',                               // default
            xl('Initial Cost')
        );

        $GLOBALS_METADATA['Postal Letter']['POSTAL_LETTER_ADDITIONAL_COST'] = array(
            xl('Additional Page Cost'),
            'text',                           // data type
            '',                               // default
            xl('Additional Page Cost')
        );

        $GLOBALS_METADATA['Postal Letter']['POSTAL_LETTER_LIMIT_COST'] = array(
            xl('Limit Cost'),
            'text',                           // data type
            '',                               // default
            xl('Limit Cost')
        );

		// Configure For Email Verification API
        $GLOBALS_METADATA['Email Verification']['email_verification_api'] = array(
            xl('Email Verification API'),
            'text',                           // data type
            '',                               // default
            xl('Email Verification API')
        );
	}

	public static function CoverageCheck(&$GLOBALS_METADATA, &$USER_SPECIFIC_TABS, &$USER_SPECIFIC_GLOBALS) {
		// Configure For Postal Letter
		$GLOBALS_METADATA['Availity']['serviceType'] = array(
            xl('Service Type for Coverage Eligibility Check'),
            'text',                           // data type
            '',                               // default
            xl('Service Type for coverage eligibility')
        );

        $GLOBALS_METADATA['Availity']['default_provider'] = array(
            xl('Default provider for Coverage Eligibility Check'),
            'text',                           // data type
            '',                               // default
            xl('Default provider for Coverage Eligibility Check')
        );

        $GLOBALS_METADATA['Availity']['blank_provider'] = array(
            xl('Blank provider for Coverage Eligibility Check'),
            'text',                           // data type
            '',                               // default
            xl('Blank provider for Coverage Eligibility Check')
        );

        $GLOBALS_METADATA['Availity']['rq_taxidforinsurance'] = array(
            xl('Insurances which require provider federal tax id.'),
            'text',                           // data type
            '',                               // default
            xl('Insurances which require provider federal tax id.')
        );
	}
}