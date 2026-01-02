<?php

/* This is a template for printing patient statements and collection
 * letters.  You must customize it to suit your practice.  If your
 * needs are simple then you do not need programming experience to do
 * this - just read the comments and make appropriate substitutions.
 * All you really need to do is replace the [strings in brackets].
 *
 * @package OpenEMR
 * @author Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2006 Rod Roark <rod@sunsetsystems.com>
 * @author Bill Cernansky <bill@mi-squared.com>
 * @copyright Copyright (c) 2009 Bill Cernansky <bill@mi-squared.com>
 * @author Tony McCormick <tony@mi-squared.com>
 * @copyright Copyright (c) 2009 Tony McCormick <tony@mi-squared.com>
 * @author Raymond Magauran <magauran@medfetch.com>
 * @copyright Copyright (c) 2016 Raymond Magauran <magauran@medfetch.com>
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Jerry Padgett <sjpadgett@gmail.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2020 Stephen Waite <stephen.waite@cmsvt.com>
 * @author Daniel Pflieger <daniel@growlingflea.com>
 * @copyright Copyright (c) 2018 Daniel Pflieger <daniel@growlingflea.com>
 * @author Sherwin Gaddis <sherwingaddis@gmail.com> (twig conversion)
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Globals\OEGlobalsBag;

// The location/name of a temporary file to hold printable statements.
// May want to alter these names to allow multi-site installs out-of-the-box

$globalsBag = OEGlobalsBag::getInstance();
$STMT_TEMP_FILE = $globalsBag->get('temporary_files_dir') . "/openemr_statements.txt";
$STMT_TEMP_FILE_PDF = $globalsBag->get('temporary_files_dir') . "/openemr_statements.pdf";
$moreSec = $globalsBag->get('more_secure');
$STMT_PRINT_CMD = (new CryptoGen())->decryptStandard($moreSec['print_command']);

/** There are two options to print a batch of PDF statements:
 *  1.  The original statement, a text based statement, using CezPDF
 *      Altering this statement is labor intensive, but capable of being altered any way desired...
 *
 *  2.  Branded Statement, whose core is build from 1., the original statement, using mPDF.
 *
 *      To customize 2., add your practice location/images/practice_logo.gif
 *      In the base/default install this is located at '/openemr/sites/default/images/practice_logo.gif',
 *      Adjust directory paths per your installation.
 *      Further customize 2. manually in functions report_2() and create_HTML_statement(), below.
 *
 */
function make_statement($stmt)
{
    $globalsBag = OEGlobalsBag::getInstance();
    if ($globalsBag->get('statement_appearance') == "1") {
        if (!empty($_POST['form_portalnotify']) && is_auth_portal($stmt['pid'])) {
            return osp_create_HTML_statement($stmt);
        } else {
            return create_HTML_statement($stmt);
        }
    } else {
        return create_statement($stmt);
    }
}

/**
 * This prints a header for documents.  Keeps the brand uniform...
 * @param string $pid patient_id
 * @param string $direction , options "web" or anything else.  Web provides apache-friendly url links.
 * @return string to be displayed however requested
 */
function report_header_2($stmt, $providerID = '1')
{
    //start of AI generated code by Codeium
    $titleres = getPatientData($stmt['pid'], "fname,lname,DOB");
    $facility = QueryUtils::querySingleRow(
        "SELECT f.name, f.street, f.city, f.state, f.postal_code, f.phone, f.fax "
        . "FROM form_encounter fe JOIN facility f ON fe.facility_id = f.id WHERE fe.id = ?",
        [$stmt['fid']]
    );

    $globalsBag = OEGlobalsBag::getInstance();
    $practice_logo = !empty($globalsBag->get('statement_logo'))
        ? $globalsBag->get('OE_SITE_DIR') . "/images/" . convert_safe_file_dir_name($globalsBag->get('statement_logo'))
        : $globalsBag->get('OE_SITE_DIR') . "/images/practice_logo.gif";

    $data = [
        'facility' => [
            'name' => text($facility['name']),
            'street' => text($facility['street']),
            'city' => text($facility['city']),
            'state' => text($facility['state']),
            'postal_code' => text($facility['postal_code']),
            'phone' => text($facility['phone']),
            'fax' => text($facility['fax']),
        ],
        'patient' => [
            'fname' => text($titleres['fname']),
            'lname' => text($titleres['lname']),
            'pid' => text($stmt['pid']),
        ],
        'generated_on' => text(oeFormatShortDate()),
        'provider_name' => text(getProviderName($providerID)),
        'practice_logo' => is_file($practice_logo) ? attr($practice_logo) : null,
        'have_logo' => is_file($practice_logo),
    ];
    //end of AI generated code
    $globalsBag = OEGlobalsBag::getInstance();
    return (new TwigContainer(null, $globalsBag->get('kernel')))->getTwig()->render('statements/statement_header.html.twig', $data);
}

/**
 * @throws \Twig\Error\RuntimeError
 * @throws \Twig\Error\SyntaxError
 * @throws \Twig\Error\LoaderError
 */
function create_HTML_statement($stmt)
{
    if (!$stmt['pid']) {
        return ""; // get out if no data
    }

    $globalsBag = OEGlobalsBag::getInstance();
    #minimum_amount_due_to _print
    if ($stmt['amount'] <= ($globalsBag->get('minimum_amount_to_print')) && $globalsBag->get('use_statement_print_exclusion') && ($_POST['form_category'] !== "All")) {
        return "";
    }

    // Facility and Billing Locations
    $facility_row = QueryUtils::querySingleRow(
        "SELECT f.name, f.street, f.city, f.state, f.postal_code "
        . "FROM form_encounter fe JOIN facility f ON fe.facility_id = f.id WHERE fe.id = ?",
        [$stmt['fid']]
    );
    $clinic_name = $facility_row['name'];
    $clinic_addr = $facility_row['street'];
    $clinic_csz = "{$facility_row['city']}, {$facility_row['state']}, {$facility_row['postal_code']}";

    $billing_row = QueryUtils::querySingleRow(
        "SELECT f.name, f.street, f.city, f.state, f.postal_code "
        . "FROM form_encounter fe JOIN facility f ON fe.billing_facility = f.id WHERE fe.id = ?",
        [$stmt['fid']]
    );
    $remit_name = $billing_row['name'];
    $remit_addr = $billing_row['street'];
    $remit_csz = "{$billing_row['city']}, {$billing_row['state']}, {$billing_row['postal_code']}";

    $provider_query = QueryUtils::querySingleRow(
        "SELECT provider_id FROM form_encounter WHERE pid = ? AND encounter = ? ORDER BY id DESC LIMIT 1",
        [$stmt['pid'], $stmt['encounter']]
    );
    $providerID = $provider_query['provider_id'];
    $report_header = report_header_2($stmt, $providerID);

    // Dunning Message
    $dun_message = '';
    if ($globalsBag->get('use_dunning_message')) {
        if ($stmt['ins_paid'] != 0 || $stmt['level_closed'] == 4) {
            $age = $stmt['age'];
            if ($age <= $globalsBag->get('first_dun_msg_set')) {
                $dun_message = $globalsBag->get('first_dun_msg_text');
            } elseif ($age <= $globalsBag->get('second_dun_msg_set')) {
                $dun_message = $globalsBag->get('second_dun_msg_text');
            } elseif ($age <= $globalsBag->get('third_dun_msg_set')) {
                $dun_message = $globalsBag->get('third_dun_msg_text');
            } elseif ($age <= $globalsBag->get('fourth_dun_msg_set')) {
                $dun_message = $globalsBag->get('fourth_dun_msg_text');
            } elseif ($age >= $globalsBag->get('fifth_dun_msg_set')) {
                $dun_message = $globalsBag->get('fifth_dun_msg_text');
            }
        }
    }

    // Aging Calculation
    $num_ages = 4;
    $aging = array_fill(0, $num_ages, 0.00);  // Initialize the array
    $todays_time = strtotime(date('Y-m-d'));
    $last_activity_date = '';

    foreach ($stmt['lines'] as $line) {
        $last_activity_date = $line['dos']; // Initialize with date of service
        foreach ($line['detail'] as $dkey => $ddata) {
            $ddate = substr($dkey, 0, 10);
            if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $ddate, $matches)) {
                $ddate = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
            }

            if ($ddate > $last_activity_date) {
                $last_activity_date = $ddate;
            }
        }

        $last_activity_date = ($line['bill_date'] > $last_activity_date) ? $line['bill_date'] : $last_activity_date;
        if ($stmt['dun_count'] == '0') {
            $last_activity_date = date('Y-m-d');
            QueryUtils::sqlStatementThrowException(
                "UPDATE billing SET bill_date = ? WHERE pid = ? AND encounter = ?",
                [date('Y-m-d'), $stmt['pid'], $stmt['encounter']]
            );
        }
        $age_in_days = (int)(($todays_time - strtotime($last_activity_date)) / (60 * 60 * 24));
        $age_index = (int)(($age_in_days - 1) / 30);
        $age_index = max(0, min($num_ages - 1, $age_index));
        $aging[$age_index] += $line['amount'] - $line['paid'];
    }

    // Aging Line
    $ageline = xl('Current') . ': ' . sprintf("%.2f", $aging[0]);
    for ($age_index = 1; $age_index < ($num_ages - 1); ++$age_index) {
        $ageline .= ' | ' . ($age_index * 30 + 1) . '-' . ($age_index * 30 + 30) . ':' . sprintf(" %.2f", $globalsBag->get('gbl_currency_symbol') . '' . $aging[$age_index]);
    }
    if ($globalsBag->get('show_aging_on_custom_statement')) {
        $ageline .= ' | ' . xl('Over') . ' ' . ($age_index * 30) . ':' . sprintf(" %.2f", $aging[$age_index]);
    }

    // Appointments
    $num_appts = $globalsBag->get('number_appointments_on_statement');
    $events = [];
    if ($num_appts != 0) {
        $next_day = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'));
        $current_date2 = date('Y-m-d', $next_day);
        $events = fetchNextXAppts($current_date2, $stmt['pid'], $num_appts);
    }
    $statement_message = $globalsBag->get('statement_msg_text') ?? '';
    $billing_phone_number = $globalsBag->get('billing_phone_number') ?? $facility_row['phone'] ?? '';

    $data = [
        'stmt' => $stmt,
        'report_header' => $report_header,
        'use_dunning_message' => $globalsBag->get('use_dunning_message'),
        'ins_paid' => $stmt['ins_paid'],
        'level_closed' => $stmt['level_closed'],
        'age' => $stmt['age'] ?? null,
        'first_dun_msg_set' => $globalsBag->get('first_dun_msg_set') ?? null,
        'second_dun_msg_set' => $globalsBag->get('second_dun_msg_set') ?? null,
        'third_dun_msg_set' => $globalsBag->get('third_dun_msg_set') ?? null,
        'fourth_dun_msg_set' => $globalsBag->get('fourth_dun_msg_set') ?? null,
        'fifth_dun_msg_set' => $globalsBag->get('fifth_dun_msg_set') ?? null,
        'first_dun_msg_text' => $globalsBag->get('first_dun_msg_text') ?? '',
        'second_dun_msg_text' => $globalsBag->get('second_dun_msg_text') ?? '',
        'third_dun_msg_text' => $globalsBag->get('third_dun_msg_text') ?? '',
        'fourth_dun_msg_text' => $globalsBag->get('fourth_dun_msg_text') ?? '',
        'fifth_dun_msg_text' => $globalsBag->get('fifth_dun_msg_text') ?? '',
        'dun_message' => $dun_message,
        'aging' => $aging,
        'ageline' => $ageline,
        'clinic_name' => $clinic_name,
        'clinic_addr' => $clinic_addr,
        'clinic_csz' => $clinic_csz,
        'remit_name' => $remit_name,
        'remit_addr' => $remit_addr,
        'remit_csz' => $remit_csz,
        'events' => $events,
        'number_appointments_on_statement' => $globalsBag->get('number_appointments_on_statement'),
        'show_aging_on_custom_statement' => $globalsBag->get('show_aging_on_custom_statement'),
        'statement_message' => $statement_message,
        'billing_phone_number' => $billing_phone_number,
        'label_payby' => xl('If paying by'),
        'label_cards' => xl('VISA/MC/Discovery/HSA'),
        'label_cardnum' => xl('Card'),
        'label_expiry' => xl('Exp'),
        'label_cvv' => xl('CVV'),
        'label_sign' => xl('Signature'),
        'label_retpay' => xl('Please fill in credit information and send for review.'),
        'practice_cards_path' => $globalsBag->get('OE_SITE_DIR') . "/images/visa_mc_disc_credit_card_logos_176x35.gif",
        'has_practice_cards' => file_exists($globalsBag->get('OE_SITE_DIR') . "/images/visa_mc_disc_credit_card_logos_176x35.gif"),
    ];
    // Register the filter with Twig
    $twig = (new TwigContainer(null, $globalsBag->get('kernel')))->getTwig();
    return $twig->render('statements/statement_content.html.twig', $data);
}//end of create html statement

// This function builds a printable statement or collection letter from
// an associative array having the following keys:
//
//  today   = statement date yyyy-mm-dd
//  pid     = patient ID
//  patient = patient name
//  amount  = total amount due
//  to      = array of addressee name/address lines
//  lines   = array of lines, each with the following keys:
//    dos     = date of service yyyy-mm-dd
//    desc    = description
//    amount  = charge less adjustments
//    paid    = amount paid
//    notice  = 1 for first notice, 2 for second, etc.
//    detail  = associative array of details
//
// Each detail array is keyed on a string beginning with a date in
// yyyy-mm-dd format, or blanks in the case of the original charge
// items.  Its values are associative arrays like this:
//
//  pmt - payment amount as a positive number, only for payments
//  src - check number or other source, only for payments
//  chg - invoice line item amount amount, only for charges or
//        adjustments (adjustments may be zero)
//  rsn - adjustment reason, only for adjustments
//
// The returned value is a string that can be sent to a printer.
// This example is plain text, but if you are a hotshot programmer
// then you could make a PDF or PostScript or whatever peels your
// banana.  These strings are sent in succession, so append a form
// feed if that is appropriate.
//

// A sample of the text based format follows:

//[Your Clinic Name]             Patient Name          2009-12-29
//[Your Clinic Address]          Chart Number: 1848
//[City, State Zip]              Insurance information on file
//
//
//ADDRESSEE                      REMIT TO
//Patient Name                     [Your Clinic Name]
//patient address                  [Your Clinic Address]
//city, state zipcode              [City, State Zip]
//                                 If paying by VISA/MC/AMEX/Dis
//
//Card_____________________  Exp______ Signature___________________
//                     Return above part with your payment
//-----------------------------------------------------------------
//
//_______________________ STATEMENT SUMMARY _______________________
//
//Visit Date  Description                                    Amount
//
//2009-08-20  Procedure 99345                                198.90
//            Paid 2009-12-15:                               -51.50
//... more details ...
//...
//...
// skipping blanks in example
//
//
//Name: Patient Name              Date: 2009-12-29     Due:   147.40
//_________________________________________________________________
//
//Please call if any of the above information is incorrect
//We appreciate prompt payment of balances due
//
//[Your billing contact name]
//  Billing Department
//  [Your billing dept phone]

function create_statement($stmt)
{
    if (!$stmt['pid']) {
        return ""; // Exit if no data
    }

    $globalsBag = OEGlobalsBag::getInstance();
    if ($stmt['amount'] <= ($globalsBag->get('minimum_amount_to_print')) && $globalsBag->get('use_statement_print_exclusion')) {
        return "";
    }

    // Fetch clinic and billing location details
    $clinic = QueryUtils::querySingleRow(
        "SELECT f.name, f.street, f.city, f.state, f.postal_code, f.phone, f.fax "
        . "FROM form_encounter fe JOIN facility f ON fe.facility_id = f.id WHERE fe.id = ?",
        [$stmt['fid']]
    );
    $billing = QueryUtils::querySingleRow(
        "SELECT f.name, f.street, f.city, f.state, f.postal_code "
        . "FROM form_encounter fe JOIN facility f ON fe.billing_facility = f.id WHERE fe.id = ?",
        [$stmt['fid']]
    );

    // Fetch billing contact info
    $contact = QueryUtils::querySingleRow(
        "SELECT f.attn, f.phone FROM facility f "
        . "LEFT JOIN users u ON f.id=u.facility_id "
        . "LEFT JOIN billing b ON b.provider_id=u.id AND b.pid = ? "
        . "WHERE billing_location=1",
        [$stmt['pid']]
    );

    // Dunning messages based on invoice age
    $dun_message = "";
    if ($globalsBag->get('use_dunning_message') && ($stmt['ins_paid'] != 0 || $stmt['level_closed'] == 4)) {
        foreach (
            [
                'first' => 'first_dun_msg_set',
                'second' => 'second_dun_msg_set',
                'third' => 'third_dun_msg_set',
                'fourth' => 'fourth_dun_msg_set',
                'fifth' => 'fifth_dun_msg_set'
            ] as $key => $globalVar
        ) {
            if ($stmt['age'] <= $globalsBag->get($globalVar)) {
                $dun_message = $globalsBag->get("{$key}_dun_msg_text");
                break;
            }
        }
    }

    // Calculate aging buckets for outstanding balance
    $aging = array_fill(0, 4, 0.00);
    $today = strtotime(date('Y-m-d'));

    foreach ($stmt['lines'] as &$line) {
        $line['desc'] = ($globalsBag->get('use_custom_statement')) ? substr($line['desc'], 0, 30) : $line['desc'];
        if (strpos($line['desc'], 'Procedure 992') === 0) {
            $line['desc'] = str_replace("Procedure", "Office Visit:", $line['desc']);
        }

        // Calculate aging index
        $age_days = (int)(($today - strtotime($line['dos'])) / (60 * 60 * 24));
        $age_index = min(3, max(0, (int)(($age_days - 1) / 30)));
        $aging[$age_index] += $line['amount'] - $line['paid'];
    }

    // Prepare data array for Twig rendering
    $data = [
        'clinic' => $clinic,
        'billing' => $billing,
        'contact' => $contact,
        'stmt' => $stmt,
        'dun_message' => $dun_message,
        'aging' => $aging,
        'generated_on' => oeFormatShortDate(),
        'provider_name' => getProviderName($stmt['provider_id']),
    ];

    return (new TwigContainer(null, $globalsBag->get('kernel')))->getTwig()->render('statements/statement_content.html.twig', $data);
}


/**
 * Create patient portal statement using Twig template
 * @param array $stmt Statement data array
 * @return string Rendered HTML statement
 * @throws \Twig\Error\LoaderError
 * @throws \Twig\Error\RuntimeError
 * @throws \Twig\Error\SyntaxError
 */
function osp_create_HTML_statement($stmt)
{
    if (!$stmt['pid']) {
        return ""; // get out if no data
    }

    $globalsBag = OEGlobalsBag::getInstance();
    #minimum_amount_due_to _print
    if ($stmt['amount'] <= ($globalsBag->get('minimum_amount_to_print')) && $globalsBag->get('use_statement_print_exclusion')) {
        return "";
    }

    // Facility (service location)
    $row = QueryUtils::querySingleRow(
        "SELECT f.name, f.street, f.city, f.state, f.postal_code, f.attn, f.phone "
        . "FROM facility f "
        . "LEFT JOIN users u ON f.id=u.facility_id "
        . "LEFT JOIN billing b ON b.provider_id=u.id AND b.pid = ? "
        . "WHERE service_location=1",
        [$stmt['pid']]
    );
    $clinic_name = $row['name'];
    $clinic_addr = $row['street'];
    $clinic_csz = "{$row['city']}, {$row['state']}, {$row['postal_code']}";
    $billing_contact = $row['attn'];
    $billing_phone = $row['phone'];
    $remit_name = $clinic_name;
    $remit_addr = $clinic_addr;
    $remit_csz = $clinic_csz;

    $find_provider = QueryUtils::querySingleRow(
        "SELECT provider_id FROM form_encounter "
        . "WHERE pid = ? AND encounter = ? "
        . "ORDER BY id DESC LIMIT 1",
        [$stmt['pid'], $stmt['encounter']]
    );
    $providerID = $find_provider['provider_id'];
    $report_header = report_header_2($stmt, $providerID);

    // Dunning message setup
    $dun_message = '';
    if ($globalsBag->get('use_dunning_message')) {
        if ($stmt['ins_paid'] != 0 || $stmt['level_closed'] == 4) {
            $age = $stmt['age'];
            if ($age <= $globalsBag->get('first_dun_msg_set')) {
                $dun_message = $globalsBag->get('first_dun_msg_text');
            } elseif ($age <= $globalsBag->get('second_dun_msg_set')) {
                $dun_message = $globalsBag->get('second_dun_msg_text');
            } elseif ($age <= $globalsBag->get('third_dun_msg_set')) {
                $dun_message = $globalsBag->get('third_dun_msg_text');
            } elseif ($age <= $globalsBag->get('fourth_dun_msg_set')) {
                $dun_message = $globalsBag->get('fourth_dun_msg_text');
            } elseif ($age >= $globalsBag->get('fifth_dun_msg_set')) {
                $dun_message = $globalsBag->get('fifth_dun_msg_text');
            }
        }
    }

    // Calculate aging
    $num_ages = 4;
    $aging = [];
    for ($age_index = 0; $age_index < $num_ages; ++$age_index) {
        $aging[$age_index] = 0.00;
    }
    
    $todays_time = strtotime(date('Y-m-d'));
    
    foreach ($stmt['lines'] as $line) {
        $dos = $line['dos'];
        $age_in_days = (int)(($todays_time - strtotime($dos)) / (60 * 60 * 24));
        $age_index = (int)(($age_in_days - 1) / 30);
        $age_index = max(0, min($num_ages - 1, $age_index));
        $aging[$age_index] += $line['amount'] - $line['paid'];
    }

    // Generate aging line
    $ageline = xl('Current') . ': ' . sprintf("%.2f", $aging[0]);
    for ($age_index = 1; $age_index < ($num_ages - 1); ++$age_index) {
        $ageline .= ' | ' . ($age_index * 30 + 1) . '-' . ($age_index * 30 + 30) . ':' .
            sprintf(" %.2f", $globalsBag->get('gbl_currency_symbol') . '' . $aging[$age_index]);
    }
    
    if ($globalsBag->get('show_aging_on_custom_statement')) {
        $ageline .= ' | ' . xl('Over') . ' ' . ($age_index * 30) . ':' .
            sprintf(" %.2f", $aging[$age_index]);
    }
    
    // Appointments
    $events = [];
    if ($globalsBag->get('number_appointments_on_statement') != 0) {
        $next_day = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'));
        $current_date2 = date('Y-m-d', $next_day);
        $events = fetchNextXAppts($current_date2, $stmt['pid'], $globalsBag->get('number_appointments_on_statement'));
    }
    
    $statement_message = $globalsBag->get('statement_msg_text') ?? '';
    $label_bill_phone = (!empty($globalsBag->get('billing_phone_number')) ? $globalsBag->get('billing_phone_number') : $billing_phone);
    $practice_cards_path = $globalsBag->get('OE_SITE_DIR') . "/images/visa_mc_disc_credit_card_logos_176x35.gif";
    
    // Prepare data for Twig
    $data = [
        'stmt' => $stmt,
        'report_header' => $report_header,
        'use_dunning_message' => $globalsBag->get('use_dunning_message'),
        'dun_message' => $dun_message,
        'use_custom_statement' => $globalsBag->get('use_custom_statement'),
        'billing_contact' => $billing_contact,
        'label_bill_phone' => $label_bill_phone,
        'statement_message_to_patient' => $globalsBag->get('statement_message_to_patient'),
        'statement_message' => $statement_message,
        'statement_bill_note_print' => $globalsBag->get('statement_bill_note_print'),
        'show_aging_on_custom_statement' => $globalsBag->get('show_aging_on_custom_statement'),
        'ageline' => $ageline,
        'number_appointments_on_statement' => $globalsBag->get('number_appointments_on_statement'),
        'events' => $events,
        'remit_name' => $remit_name,
        'remit_addr' => $remit_addr,
        'remit_csz' => $remit_csz,
        'practice_cards_exist' => file_exists($practice_cards_path),
    ];
    
    $twig = (new TwigContainer(null, $globalsBag->get('kernel')))->getTwig();
    return $twig->render('statements/portal_statement_content.html.twig', $data);
}
