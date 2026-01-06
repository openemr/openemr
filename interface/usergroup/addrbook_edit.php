<?php

/**
 * addrbook_edit.php - Enhanced with NPI Lookup
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2006-2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'practice')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Address Book")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Collect user id if editing entry
$userid = $_REQUEST['userid'] ?? '';

// Collect type if creating a new entry
$type = $_REQUEST['type'] ?? '';

$info_msg = "";

function invalue($name)
{
    if (empty($_POST[$name])) {
        return "''";
    }

    $fld = add_escape_custom(trim((string) $_POST[$name]));
    return "'$fld'";
}

?>
<html>
<head>
<title><?php echo $userid ? xlt('Edit Entry') : xlt('Add New Entry') ?></title>

    <?php Header::setupHeader(['opener']); ?>

<style>
.inputtext {
    padding-left: 2px;
    padding-right: 2px;
}
#npi-lookup-results {
    position: absolute;
    background: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    max-width: 600px;
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}
.npi-result-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}
.npi-result-item:hover {
    background-color: #f0f0f0;
}
.npi-result-item h6 {
    margin: 0 0 5px 0;
    color: #333;
}
.npi-result-item .text-muted {
    font-size: 0.85em;
}
.npi-loading {
    text-align: center;
    padding: 20px;
}
.npi-error {
    padding: 10px;
    color: #dc3545;
}
</style>

<script>

 var type_options_js = Array();
    <?php
    // Collect the type options. Possible values are:
    // 1 = Unassigned (default to person centric)
    // 2 = Person Centric
    // 3 = Company Centric
    $sql = sqlStatement("SELECT option_id, option_value FROM list_options WHERE " .
    "list_id = 'abook_type' AND activity = 1");
    while ($row_query = sqlFetchArray($sql)) {
        echo "type_options_js[" . js_escape($row_query['option_id']) . "]=" . js_escape($row_query['option_value']) . ";\n";
    }
    ?>

 // Process to customize the form by type
 function typeSelect(a) {
   if(a=='ord_lab'){
      $('#cpoe_span').css('display','inline');
  } else {
       $('#cpoe_span').css('display','none');
       $('#form_cpoe').prop('checked', false);
  }
  if (type_options_js[a] == 3) {
   // Company centric:
   //   1) Hide the person Name entries
   //   2) Hide the Specialty entry
   //   3) Show the director Name entries
   $(".nameRow").hide();
   $(".specialtyRow").hide();
   $(".nameDirectorRow").show();
  }
  else {
   // Person centric:
   //   1) Hide the director Name entries
   //   2) Show the person Name entries
   //   3) Show the Specialty entry
   $(".nameDirectorRow").hide();
   $(".nameRow").show();
   $(".specialtyRow").show();
  }
 }

    let lastSearchParams = {};
    let allResults = []; // Store all fetched results
    let displayOffset = 0; // Track what we've shown to user
    let totalResultCount = 0;
    let limit = 200; // max available from NPPES

    function lookupNPI(loadMore = false) {
    const npi = $('#form_npi').val().trim();
    const firstName = $('#form_fname').val().trim();
    const lastName = $('#form_lname').val().trim();
    const organization = $('#form_organization').val().trim();

    if (!npi && !lastName && !organization) {
        alert(<?php echo xlj('Please enter search criteria'); ?>);
        return;
    }

    // Reset if new search
    if (!loadMore) {
        currentSkip = 0;
        displayOffset = 0;
        allResults = [];
        lastSearchParams = { npi, firstName, lastName, organization };
    }

    // Check if we need to fetch more from API or just display cached results
    if (displayOffset < allResults.length) {
        // We have cached results, just display next
        displayNextBatch();
        return;
    }

    // AI-generated code start (GitHub Copilot) - Refactored to use URLSearchParams
    // Need to fetch more
    const params = new URLSearchParams({
        csrf_token: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
    });
    if (npi) params.append('number', npi);
    if (firstName) params.append('first_name', firstName + '*');
    if (lastName) params.append('last_name', lastName + '*');
    if (organization) {
        params.append('organization_name', organization + '*');
        params.append('enumeration_type', 'NPI-2'); // Filter for organizations only
    }
    params.append('limit', limit);
    params.append('skip', currentSkip);

    const proxyUrl = 'npi_lookup.php?' + params.toString();
    // AI-generated code end
    const resultsDiv = $('#npi-lookup-results');

    if (loadMore) {
        resultsDiv.append('<div class="npi-loading"><i class="fa fa-spinner fa-spin"></i>' + jsText(xl("Loading more ...")) + '</div>');
    } else {
        resultsDiv.show().html('<div class="npi-loading"><i class="fa fa-spinner fa-spin"></i>' + jsText(xl("Searching...")) + '</div>');

    }

        fetch(proxyUrl)
            .then(response => response.json())
            .then(data => {
                if (data.error) throw new Error(data.error);

                // Store the new results
                allResults = allResults.concat(data.results);
                totalResultCount = data.result_count;
                currentSkip += 200; // Move API skip forward by 200

                // Display first results of the new batch
                displayNextBatch();
            })
            .catch(error => {
                $('.npi-loading').remove();
                resultsDiv.html('<div class="npi-error">' + jsText(xl("Error")) + ': ' + jsText(xl(error.message)) + '</div>');
            });
    }

    function displayNextBatch() {
        const resultsDiv = $('#npi-lookup-results');

        // Remove loading indicator
        $('.npi-loading').remove();

        const startIdx = displayOffset;
        const endIdx = Math.min(displayOffset + 50, allResults.length);
        const batch = allResults.slice(startIdx, endIdx);

        let html = '';

        if (displayOffset === 0) {
            // First display - show header
            html += `<div style="padding: 10px;">
                <h6>${jsText(xl("Select a Provider"))} (${jsText(totalResultCount)} ${jsText(xl("total results"))})</h6>
            </div>`;
        }

        // Add results
        batch.forEach(result => {
            const basic = result.basic;
            const addr = result.addresses?.find(a => a.address_purpose === 'LOCATION') || result.addresses?.[0];
            const taxonomy = result.taxonomies?.[0];
            const isOrg = result.enumeration_type === 'NPI-2';
            const name = isOrg ? basic.organization_name :
                            `${basic.first_name || ''} ${basic.middle_name || ''} ${basic.last_name || ''}`.trim();

            html += `<div class="npi-result-item" onclick='fillNPIData(${JSON.stringify(result)})'>
                <h6>${jsText(name)}</h6>
                <div class="text-muted">
                    <strong>${jsText('NPI')}: </strong>${jsText(result.number)}<br>
                    ${jsText(taxonomy) ? `<strong>${jsText(xl('Specialty'))}: </strong>${jsText(xl(taxonomy.desc))}<br>` : ''}
                    ${jsText(addr) ? `<strong>${jsText(xl('Address'))}: </strong>${jsText(addr.address_1)} , ${jsText(addr.city)} , ${jsText(addr.state)} ${jsText(addr.postal_code)}` : ''}
                </div>
            </div>`;
        });

        displayOffset = endIdx; // Update display offset

        // Show Load More button if there are more results (either cached or on server)
        if (displayOffset < allResults.length || displayOffset < totalResultCount) {
            html += `<div style="padding: 10px; text-align: center;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="lookupNPI(true)">
                    ${jsText(xl('Load More Results'))} (${jsText(xl('showing'))} ${jsText(displayOffset)} ${jsText(xl('of'))} ${jsText(totalResultCount)})
                </button>
            </div>`;
        }

        if (displayOffset === 50 && startIdx === 0) {
            resultsDiv.html(html); // First display
        } else {
            resultsDiv.append(html); // Append to existing
        }
    }

    function displayNPIResults(data, append = false) {
        const resultsDiv = $('#npi-lookup-results');

        // ALWAYS remove loading spinner first, regardless of append mode
        $('.npi-loading').remove();

        if (!data.results || data.results.length === 0) {
        if (!append) {
            resultsDiv.html('<div class="npi-error">' + jsText(xl('No results found')) + '</div>');
            setTimeout(() => resultsDiv.hide(), 3000);
        }
        return;
        }

        let html = '';

        if (!append) {
            html += `<div style="padding: 10px;">
            <h6>${jsText(xl('Select a Provider'))} (${jsText(data.result_count)} ${jsText(xl('total results'))} , ${jsText(xl('showing'))} ${Math.min(currentSkip + data.results.length, data.result_count)})</h6>
            </div>`;
        }

        data.results.forEach(result => {
            const basic = result.basic;
            const addr = result.addresses?.find(a => a.address_purpose === 'LOCATION') || result.addresses?.[0];
            const taxonomy = result.taxonomies?.[0];

            // Determine if individual or organization
            const isOrg = result.enumeration_type === 'NPI-2';
            const name = isOrg ? basic.organization_name :
                        `${basic.first_name || ''} ${basic.middle_name || ''} ${basic.last_name || ''}`.trim();

            html += `<div class="npi-result-item" onclick='fillNPIData(${JSON.stringify(result)})'>
                <h6>${jsText(name)}</h6>
                <div class="text-muted">
                    <strong>${jsText('NPI')}: </strong>${jsText(result.number)}<br>
                    ${jsText(taxonomy) ? `<strong>${jsText(xl(Specialty))}: </strong>${jsText(xl(taxonomy.desc))}<br>` : ''}
                    ${jsText(addr)} ? <strong>${jsText(xl('Address'))}: </strong>${jsText(addr.address_1)} , ${jsText(addr.city)}, ${jsText(addr.state)} ${jsText(addr.postal_code)} : ''
                </div>
            </div>`;
        });

        // Load More button
        if (data.result_count > currentSkip + data.results.length) {
            html += `<div style="padding: 10px; text-align: center;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="lookupNPI(true)">
                    ${jsText(xl('Load More Results'))}
                </button>
            </div>`;
        }

        if (append) {
            resultsDiv.append(html);
        } else {
            resultsDiv.html(html);
        }
    }

    function fillNPIData(result) {
        const basic = result.basic;
        const isOrg = result.enumeration_type === 'NPI-2';
        const location = result.addresses?.find(a => a.address_purpose === 'LOCATION') || result.addresses?.[0];
        const mailing = result.addresses?.find(a => a.address_purpose === 'MAILING');
        const taxonomy = result.taxonomies?.[0];

        // Fill Type
        $('#form_abook_type').val('external_provider');

        // Fill NPI
        $('#form_npi').val(result.number);

        // Fill name fields based on type
        if (isOrg) {
            // Organization
            $('#form_organization').val(basic.organization_name || '');
            if (basic.authorized_official_first_name) {
                $('#form_director_fname').val(basic.authorized_official_first_name);
                $('#form_director_lname').val(basic.authorized_official_last_name);
                $('#form_director_mname').val(basic.authorized_official_middle_name || '');
                $('#form_director_title').val(basic.authorized_official_title_or_position || '');
            }
        } else {
            // Individual
            $('#form_fname').val(basic.first_name || '');
            $('#form_lname').val(basic.last_name || '');
            $('#form_mname').val(basic.middle_name || '');
            $('#form_suffix').val(basic.credential || '');
            $('#form_organization').val(basic.organization_name || '');
        }

        // Fill taxonomy/specialty
        if (taxonomy) {
            $('#form_taxonomy').val(taxonomy.code || '');
            $('#form_specialty').val(taxonomy.desc || '');
        }

        // Fill location address (main address)
        if (location) {
            $('#form_street').val(location.address_1 || '');
            $('#form_streetb').val(location.address_2 || '');
            $('#form_city').val(location.city || '');
            $('#form_state').val(location.state || '').trigger('change');
            $('#form_zip').val(location.postal_code || '');
            $('#form_phonew1').val(location.telephone_number || '');
            $('#form_fax').val(location.fax_number || '');
        }

        // Fill mailing address (alt address) if different
        if (mailing) {
            $('#form_street2').val(mailing.address_1 || '');
            $('#form_streetb2').val(mailing.address_2 || '');
            $('#form_city2').val(mailing.city || '');
            $('#form_state2').val(mailing.state || '').trigger('change');
            $('#form_zip2').val(mailing.postal_code || '');
        }

        // Hide results
        $('#npi-lookup-results').hide();

        // Show success message
        alert(<?php echo xlj('Provider information populated from NPPES Registry'); ?>);
    }

    // Close results when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#npi-lookup-results, #btn-npi-lookup').length) {
            $('#npi-lookup-results').hide();
        }
    });
</script>

</head>

<body class="body_top">
<?php
 // If we are saving, then save and close the window.
 //
if (!empty($_POST['form_save'])) {
 // Collect the form_abook_type option value
 //  (ie. patient vs company centric)
    $type_sql_row = sqlQuery("SELECT `option_value` FROM `list_options` WHERE `list_id` = 'abook_type' AND `option_id` = ? AND activity = 1", [trim((string) $_POST['form_abook_type'])]);
    $option_abook_type = $type_sql_row['option_value'] ?? '';
 // Set up any abook_type specific settings
    if ($option_abook_type == 3) {
        // Company centric
        $form_title = invalue('form_director_title');
        $form_fname = invalue('form_director_fname');
        $form_lname = invalue('form_director_lname');
        $form_mname = invalue('form_director_mname');
        $form_suffix = invalue('form_director_suffix');
    } else {
        // Person centric
        $form_title = invalue('form_title');
        $form_fname = invalue('form_fname');
        $form_lname = invalue('form_lname');
        $form_mname = invalue('form_mname');
        $form_suffix = invalue('form_suffix');
    }

    if ($userid) {
        $query = "UPDATE users SET " .
        "abook_type = "   . invalue('form_abook_type')   . ", " .
        "title = "        . $form_title                  . ", " .
        "fname = "        . $form_fname                  . ", " .
        "lname = "        . $form_lname                  . ", " .
        "mname = "        . $form_mname                  . ", " .
        "suffix = "       . $form_suffix                 . ", " .
        "specialty = "    . invalue('form_specialty')    . ", " .
        "organization = " . invalue('form_organization') . ", " .
        "valedictory = "  . invalue('form_valedictory')  . ", " .
        "assistant = "    . invalue('form_assistant')    . ", " .
        "federaltaxid = " . invalue('form_federaltaxid') . ", " .
        "upin = "         . invalue('form_upin')         . ", " .
        "npi = "          . invalue('form_npi')          . ", " .
        "taxonomy = "     . invalue('form_taxonomy')     . ", " .
        "cpoe = "         . invalue('form_cpoe')         . ", " .
        "email = "        . invalue('form_email')        . ", " .
        "email_direct = " . invalue('form_email_direct') . ", " .
        "url = "          . invalue('form_url')          . ", " .
        "street = "       . invalue('form_street')       . ", " .
        "streetb = "      . invalue('form_streetb')      . ", " .
        "city = "         . invalue('form_city')         . ", " .
        "state = "        . invalue('form_state')        . ", " .
        "country_code = " . invalue('form_country_code') . ", " .
        "zip = "          . invalue('form_zip')          . ", " .
        "street2 = "      . invalue('form_street2')      . ", " .
        "streetb2 = "     . invalue('form_streetb2')     . ", " .
        "city2 = "        . invalue('form_city2')        . ", " .
        "state2 = "       . invalue('form_state2')       . ", " .
        "zip2 = "         . invalue('form_zip2')         . ", " .
        "country_code2 = ". invalue('form_country_code2'). ", " .
        "phone = "        . invalue('form_phone')        . ", " .
        "phonew1 = "      . invalue('form_phonew1')      . ", " .
        "phonew2 = "      . invalue('form_phonew2')      . ", " .
        "phonecell = "    . invalue('form_phonecell')    . ", " .
        "fax = "          . invalue('form_fax')          . ", " .
        "notes = "        . invalue('form_notes')        . " "  .
        "WHERE id = '" . add_escape_custom($userid) . "'";
        sqlStatement($query);
    } else {
        $userid = sqlInsert("INSERT INTO users ( " .
        "username, password, authorized, info, source, " .
        "title, fname, lname, mname, suffix, " .
        "federaltaxid, federaldrugid, upin, facility, see_auth, active, npi, taxonomy, cpoe, " .
        "specialty, organization, valedictory, assistant, billname, email, email_direct, url, " .
        "street, streetb, city, state, zip,country_code, " .
        "street2, streetb2, city2, state2, zip2,country_code2, " .
        "phone, phonew1, phonew2, phonecell, fax, notes, abook_type "            .
        ") VALUES ( "                        .
        "'', "                               . // username
        "'', "                               . // password
        "0, "                                . // authorized
        "'', "                               . // info
        "NULL, "                             . // source
        $form_title                   . ", " .
        $form_fname                   . ", " .
        $form_lname                   . ", " .
        $form_mname                   . ", " .
        $form_suffix                  . ", " .
        invalue('form_federaltaxid')  . ", " .
        "'', "                               . // federaldrugid
        invalue('form_upin')          . ", " .
        "'', "                               . // facility
        "0, "                                . // see_auth
        "1, "                                . // active
        invalue('form_npi')           . ", " .
        invalue('form_taxonomy')      . ", " .
        invalue('form_cpoe')          . ", " .
        invalue('form_specialty')     . ", " .
        invalue('form_organization')  . ", " .
        invalue('form_valedictory')   . ", " .
        invalue('form_assistant')     . ", " .
        "'', "                               . // billname
        invalue('form_email')         . ", " .
        invalue('form_email_direct')  . ", " .
        invalue('form_url')           . ", " .
        invalue('form_street')        . ", " .
        invalue('form_streetb')       . ", " .
        invalue('form_city')          . ", " .
        invalue('form_state')         . ", " .
        invalue('form_zip')           . ", " .
        invalue('form_country_code')  . ", " .
        invalue('form_street2')       . ", " .
        invalue('form_streetb2')      . ", " .
        invalue('form_city2')         . ", " .
        invalue('form_state2')        . ", " .
        invalue('form_zip2')          . ", " .
        invalue('form_country_code2') . ", " .
        invalue('form_phone')         . ", " .
        invalue('form_phonew1')       . ", " .
        invalue('form_phonew2')       . ", " .
        invalue('form_phonecell')     . ", " .
        invalue('form_fax')           . ", " .
        invalue('form_notes')         . ", " .
        invalue('form_abook_type')    . " "  .
        ")");
    }
} elseif (!empty($_POST['form_delete'])) {
    if ($userid) {
       // Be careful not to delete internal users.
        sqlStatement("DELETE FROM users WHERE id = ? AND (username = '' OR username IS NULL)", [$userid]);
    }
}

if (!empty($_POST['form_save']) || !empty($_POST['form_delete'])) {
  // Close this window and redisplay the updated list.
    echo "<script>\n";
    if ($info_msg) {
        echo " alert(" . js_escape($info_msg) . ");\n";
    }

    echo " window.close();\n";
    echo " if (opener.refreshme) opener.refreshme();\n";
    echo "</script></body></html>\n";
    exit();
}

if ($userid) {
    $row = sqlQuery("SELECT * FROM users WHERE id = ?", [$userid]);
}

if ($type) { // note this only happens when its new
  // Set up type
    $row['abook_type'] = $type;
}

?>

<script>
 $(function () {
  // customize the form via the type options
  typeSelect(<?php echo js_escape($row['abook_type'] ?? null); ?>);
  if(typeof abook_type != 'undefined' && abook_type == 'ord_lab') {
    $('#cpoe_span').css('display','inline');
   }
 });
</script>

<form method='post' name='theform' id="theform" action='addrbook_edit.php?userid=<?php echo attr_url($userid) ?>'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<!-- NPI Lookup Results Container -->
<div id="npi-lookup-results"></div>

<?php if (AclMain::aclCheckCore('admin', 'practice')) { // allow choose type option if have admin access ?>
<div class="form-row">
    <div class='col-2'>
        <label class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Type'); ?>:</label>
    </div>
    <div class="col">
        <?php echo generate_select_list('form_abook_type', 'abook_type', ($row['abook_type'] ?? null), '', 'Unassigned', 'form-control-sm', 'typeSelect(this.value)'); ?>
    </div>
</div>
<?php } // end of if has admin access ?>

<div class="form-row nameRow my-1">
    <div class="col-auto">
        <label for="title" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Name'); ?>:</label>
    </div>
    <div class="col-auto">
        <?php generate_form_field(['data_type' => 1,'field_id' => 'title','smallform' => 'true','list_id' => 'titles','empty_title' => ' '], ($row['title'] ?? '')); ?>
    </div>
    <div class="col-auto">
        <label for="form_lname" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Last{{Name}}'); ?>:</label>
    </div>
    <div class="col-auto">
        <input type='text' size='10' id='form_lname' name='form_lname' class='form-control form-control-sm inputtext' maxlength='50' value='<?php echo attr($row['lname'] ?? ''); ?>'/>
    </div>
    <div class="col-auto">
        <label for="form_fname" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('First{{Name}}'); ?>:</label>
    </div>
    <div class="col-auto">
        <input type='text' size='10' id='form_fname' name='form_fname' class='form-control form-control-sm inputtext' maxlength='50' value='<?php echo attr($row['fname'] ?? ''); ?>' />
    </div>
    <div class="col-auto">
        <label for="form_mname" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Middle{{Name}}'); ?>:</label>
    </div>
    <div class="col-auto">
        <input type='text' size='4' id='form_mname' name='form_mname' class='form-control form-control-sm inputtext' maxlength='50' value='<?php echo attr($row['mname'] ?? ''); ?>' />
    </div>
    <div class="col-auto">
        <label for="form_suffix" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Suffix'); ?>:</label>
    </div>
    <div class="col-auto">
        <input type='text' size='4' id='form_suffix' name='form_suffix' class='form-control form-control-sm inputtext' maxlength='50' value='<?php echo attr($row['suffix'] ?? ''); ?>' />
    </div>
</div>

<div class="form-row specialtyRow my-1">
    <div class="col-2">
        <label for="form_specialty" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Specialty'); ?>:</label>
    </div>
    <div class="col">
        <input type='text' size='40' id='form_specialty' name='form_specialty' maxlength='250' value='<?php echo attr($row['specialty'] ?? ''); ?>' class='form-control form-control-sm inputtext w-100' />
    </div>
</div>

<div class="form-row my-1">
    <div class="col-2">
        <label for="form_organization" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Organization'); ?>:</label>
    </div>
    <div class="col">
        <input type='text' size='40' id='form_organization' name='form_organization' maxlength='250' value='<?php echo attr($row['organization'] ?? ''); ?>' class='form-control form-control-sm inputtext' />
    <span id='cpoe_span' style="display:none;">
        <input type='checkbox' title="<?php echo xla('CPOE'); ?>" name='form_cpoe' id='form_cpoe' value='1' <?php echo (!empty($row['cpoe']) && ($row['cpoe'] == '1')) ? "CHECKED" : ""; ?>/>
        <label for='form_cpoe' class="font-weight-bold"><?php echo xlt('CPOE'); ?></label>
   </span>
    </div>
</div>
<div class="nameDirectorRow">
    <label for="director_title" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Director Name'); ?>:</label>
    <div class="form-row my-1">
        <div class="col-auto">
            <?php
            generate_form_field(['data_type' => 1,'field_id' => 'director_title','smallform' => 'true','list_id' => 'titles','empty_title' => ' '], ($row['title'] ?? ''));
            ?>
        </div>
        <div class="col-auto">
            <label for="form_director_lname" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Last{{Name}}'); ?>:</label>
        </div>
        <div class="col-auto">
            <input type='text' size='10' id='form_director_lname' name='form_director_lname' class='form-control form-control-sm inputtext' maxlength='50' value='<?php echo attr($row['lname'] ?? ''); ?>'/>
        </div>
        <div class="col-auto">
            <label for="form_director_fname" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('First{{Name}}'); ?>:</label>
        </div>
        <div class="col-auto">
            <input type='text' size='10' id='form_director_fname' name='form_director_fname' class='form-control form-control-sm inputtext' maxlength='50' value='<?php echo attr($row['fname'] ?? ''); ?>' />
        </div>
        <div class="col-auto">
            <label for="form_director_mname" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Middle{{Name}}'); ?>:</label>
        </div>
        <div class="col-auto">
            <input type='text' size='4' id='form_director_mname' name='form_director_mname' class='form-control form-control-sm inputtext' maxlength='50' value='<?php echo attr($row['mname'] ?? ''); ?>' />
        </div>
        <div class="col-auto">
            <label for="form_director_suffix" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Suffix'); ?>:</label>
        </div>
        <div class="col-auto">
            <input type='text' size='4' id='form_director_suffix' name='form_director_suffix' class='form-control form-control-sm inputtext' maxlength='50' value='<?php echo attr($row['suffix'] ?? ''); ?>' />
        </div>
    </div>
</div>

<div class="form-row my-1">
    <div class="col-2">
        <label for="form_valedictory" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Valedictory'); ?>:</label>
    </div>
    <div class="col">
        <input type='text' size='40' id='form_valedictory' name='form_valedictory' maxlength='250' value='<?php echo attr($row['valedictory'] ?? ''); ?>' class='form-control form-control-sm inputtext' />
    </div>
</div>

<div class="form-row my-1">
    <div class="col-2">
        <label for="form_phone" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Home Phone'); ?>:</label>
    </div>
    <div class="col">
        <input type='text' size='11' id='form_phone' name='form_phone' value='<?php echo attr($row['phone'] ?? ''); ?>' maxlength='30' class='form-control form-control-sm inputtext' />
    </div>
    <div class="col-2">
        <label for="form_phonecell" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Mobile'); ?>:</label>
    </div>
    <div class="col">
        <input type='text' size='11' id='form_phonecell' name='form_phonecell' maxlength='30' value='<?php echo attr($row['phonecell'] ?? ''); ?>' class='form-control form-control-sm inputtext' />
    </div>
</div>
<div class="form-row my-1">
    <div class="col-2">
        <label for="form_phonew1" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Work Phone'); ?>:</label>
    </div>
    <div class="col">
        <input type='text' size='11' id='form_phonew1' name='form_phonew1' value='<?php echo attr($row['phonew1'] ?? ''); ?>' maxlength='30' class='form-control form-control-sm inputtext' />
    </div>
    <div class="col-1">
        <label class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('2nd'); ?>:</label>
    </div>
    <div class="col">
        <input type='text' size='11' id='form_phonew2' name='form_phonew2' value='<?php echo attr($row['phonew2'] ?? ''); ?>' maxlength='30' class='form-control form-control-sm inputtext' />
    </div>
    <div class="col-1">
        <label class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Fax'); ?>:</label>
    </div>
    <div class="col">
        <input type='text' size='11' id='form_fax' name='form_fax' value='<?php echo attr($row['fax'] ?? ''); ?>' maxlength='30' class='form-control form-control-sm inputtext' />
    </div>
</div>

<div class="form-row my-1">
    <div class="col-2">
        <label for="form_assistant" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Assistant'); ?>:</label>
    </div>
    <div class="col-10">
        <input type='text' size='40' id='form_assistant' name='form_assistant' maxlength='250' value='<?php echo attr($row['assistant'] ?? ''); ?>' class='form-control form-control-sm inputtext w-100' />
    </div>
</div>

<div class="form-row my-1">
    <div class="col-2">
        <label for="form_email" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Email'); ?>:</label>
    </div>
    <div class='col-10'>
        <input type='text' size='40' id='form_email' name='form_email' maxlength='250' value='<?php echo attr($row['email'] ?? ''); ?>' class='form-control form-control-sm inputtext w-100' />
    </div>
</div>

<div class="form-row my-1">
    <div class="col-2">
        <label for="form_email_direct" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Trusted Email'); ?>:</label>
    </div>
    <div class="col-10">
        <input type='text' size='40' id='form_email_direct' name='form_email_direct' maxlength='250' value='<?php echo attr($row['email_direct'] ?? ''); ?>' class='form-control form-control-sm inputtext' />
    </div>
</div>

<div class="form-row my-1">
    <div class="col-2">
        <label for="form_url" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Website'); ?>:</label>
    </div>
    <div class="col-10">
        <input type='text' size='40' id='form_url' name='form_url' maxlength='250' value='<?php echo attr($row['url'] ?? ''); ?>' class='form-control form-control-sm inputtext' />
    </div>
</div>

<div class="form-row my-1 align-items-center">
    <div class="col-2">
        <label for="form_street form_streetb" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Main Address'); ?>:</label>
    </div>
    <div class="col-10">
        <input type='text' size='40' id='form_street' name='form_street' maxlength='60' value='<?php echo attr($row['street'] ?? ''); ?>' class='form-control form-control-sm inputtext mb-1' placeholder="<?php echo xla('Address Line 1'); ?>" />
        <input type='text' size='40' id='form_streetb' name='form_streetb' maxlength='60' value='<?php echo attr($row['streetb'] ?? ''); ?>' class='form-control form-control-sm inputtext mt-1' placeholder="<?php echo xla('Address Line 2'); ?>" />
    </div>
</div>

<div class="form-row my-1">
    <div class="col-2">
        <label for="form_city" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('City'); ?>:</label>
    </div>
    <div class="col">
        <input type='text' size='10' id='form_city' name='form_city' maxlength='30' value='<?php echo attr($row['city'] ?? ''); ?>' class='form-control form-control-sm inputtext' placeholder="<?php echo xla('City'); ?>" />
    </div>
</div>
<div class="form-row my-1">
    <div class="col-2">
        <label for="form_state" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('State') . "/" . xlt('county'); ?>:</label>
    </div>
    <div class="col">
        <?php echo generate_select_list('form_state', 'state', ($row['state'] ?? null), '', 'Unassigned', 'form-control-sm'); ?>
    </div>
    <div class="col-2">
        <label for="form_zip" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Postal code'); ?>:</label>
    </div>
    <div class="col">
        <input type='text' size='10' id='form_zip' name='form_zip' maxlength='20' value='<?php echo attr($row['zip'] ?? ''); ?>' class='form-control form-control-sm inputtext' placeholder="<?php echo xla('Postal code'); ?>" />
    </div>
    <div class="col-2">
        <label for="form_country_code" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Country'); ?>:</label>
    </div>
    <div class="col">
        <?php echo generate_select_list('form_country_code', 'country', ($row['country_code'] ?? null), '', 'Unassigned', 'form-control-sm'); ?>
    </div>
</div>

<div class="form-row my-1 align-items-center">
    <div class="col-2">
        <label for="form_street2 form_streetb2" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Alt Address'); ?>:</label>
    </div>
    <div class="col-10">
        <input type='text' size='40' id='form_street2' name='form_street2' maxlength='60' value='<?php echo attr($row['street2'] ?? ''); ?>' class='form-control form-control-sm mb-1 inputtext' placeholder="<?php echo xla('Address Line 1'); ?>" />
        <input type='text' size='40' id='form_streetb2' name='form_streetb2' maxlength='60' value='<?php echo attr($row['streetb2'] ?? ''); ?>' class='form-control form-control-sm mt-1 inputtext' placeholder="<?php echo xla('Address Line 2'); ?>" />
    </div>
</div>

<div class="form-row my-1">
    <div class="col-2">
        <label for="form_city2" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Alt City'); ?>:</label>
    </div>
    <div class="col">
        <input type='text' size='10' id='form_city2' name='form_city2' maxlength='30' value='<?php echo attr($row['city2'] ?? ''); ?>' class='form-control form-control-sm inputtext' placeholder="<?php echo xla('Alt City'); ?>" />
    </div>
</div>
<div class="form-row my-1">
    <div class="col-2">
        <label for="form_state2" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Alt State') . "/" . xlt('county'); ?>:</label>
    </div>
    <div class="col">
        <?php echo generate_select_list('form_state2', 'state', ($row['state2'] ?? null), '', 'Unassigned', 'form-control-sm'); ?>
    </div>
    <div class="col-2">
        <label for="form_zip2" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Alt Postal code'); ?>:</label>
    </div>
    <div class="col">
        <input type='text' size='10' id='form_zip2' name='form_zip2' maxlength='20' value='<?php echo attr($row['zip2'] ?? ''); ?>' class='form-control form-control-sm inputtext' placeholder="<?php echo xla('Alt Postal code'); ?>" />
    </div>
    <div class="col-2">
        <label for="form_country_code2" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Alt Country'); ?>:</label>
    </div>
    <div class="col">
        <?php echo generate_select_list('form_country_code2', 'country', ($row['country_code'] ?? null), '', 'Unassigned', 'form-control-sm'); ?>
    </div>
</div>

<div class="form-row my-1">
    <div class="col-auto">
        <label for="form_upin" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('UPIN'); ?>:</label>
    </div>
    <div class="col-auto">
        <input type='text' size='6' id='form_upin' name='form_upin' maxlength='6' value='<?php echo attr($row['upin'] ?? ''); ?>' class='form-control form-control-sm inputtext' />
   </div>
   <div class="col-auto">
        <label for="form_npi" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('NPI'); ?>:</label>
   </div>
   <div class="col-auto">
        <div class="input-group input-group-sm">
            <input type='text' size='10' id='form_npi' name='form_npi' maxlength='10' value='<?php echo attr($row['npi'] ?? ''); ?>' class='form-control form-control-sm inputtext' />
            <div class="input-group-append">
                <button type="button" id="btn-npi-lookup" class="btn btn-sm btn-info" onclick="lookupNPI()" title="<?php echo xla('Search NPPES Registry'); ?>">
                    <i class="fa fa-search"></i> <?php echo xlt('Lookup'); ?>
                </button>
            </div>
        </div>
   </div>
   <div class="col-auto">
        <label for="form_federaltaxid" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('TIN'); ?>:</label>
   </div>
   <div class="col-auto">
        <input type='text' size='10' id='form_federaltaxid' name='form_federaltaxid' maxlength='10' value='<?php echo attr($row['federaltaxid'] ?? ''); ?>' class='form-control form-control-sm inputtext' />
    </div>
    <div class="col-auto">
        <label for="form_taxonomy" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Taxonomy'); ?>:</label>
    </div>
   <div class="col-auto">
        <input type='text' size='10' id='form_taxonomy' name='form_taxonomy' maxlength='10' value='<?php echo attr($row['taxonomy'] ?? ''); ?>' class='form-control form-control-sm inputtext' />
   </div>
</div>
<div class="form-group">
    <label for="form_notes" class="font-weight-bold col-form-label col-form-label-sm"><?php echo xlt('Notes'); ?>:</label>
    <textarea rows='3' cols='40' id='form_notes' name='form_notes' wrap='virtual' class='form-control inputtext w-100'><?php echo text($row['notes'] ?? '') ?></textarea>
</div>

<br />

<input type='submit' class='btn btn-primary' name='form_save' value='<?php echo xla('Save'); ?>' />

<?php if ($userid && !$row['username']) { ?>
&nbsp;
<input type='submit' class='btn btn-danger' name='form_delete' value='<?php echo xla('Delete'); ?>' />
<?php } ?>

&nbsp;
<input type='button' class='btn btn-secondary' value='<?php echo xla('Cancel'); ?>' onclick='window.close()' />
</p>
</form>
<?php    $use_validate_js = 1;?>
<?php validateUsingPageRules($_SERVER['PHP_SELF']);?>
</body>
</html>
