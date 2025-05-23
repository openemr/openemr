<?php

/**
 * document_select.php is intended to be used in a dialog finder window for searching and selecting an individual
 * document from the calling window.
 *
 * A sample of how to open the finder is here:
 *
 * window.dlgopen('/interface/main/finder/document_select.php?csrf_token_form=csrf, '_blank', 700, 400);
 *
 * When a document is selected the file will make a call out to the calling window/iframe and call a function
 * that must be in scope of the calling window/iframe called setDocument.  setDocument has the following signature:
 * function setSelectedDocument(did:number, docName:string, categoryName:string, date:string) : void
 *
 * Note that only documents the currently logged in user has access to via the document category ACOs will be displayed
 * in the search.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\DocumentService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\SearchModifier;

if (!empty($_REQUEST)) {
    if (!CsrfUtils::verifyCsrfToken($_REQUEST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}
$searchArray = [];
$pid = $_REQUEST['pid'] ?? $_SESSION['pid'] ?? null;
$patientName = "";
if (!empty($_REQUEST['pid'])) {
    $pid = intval($_REQUEST['pid']);
    $patientService = new PatientService();
    $patientArray = $patientService->findByPid($pid);
    if (!empty($patientArray)) {
        // TODO: log the fact that pid is null
        // note for documents that foreign_id is ALWAYS the pid
        $searchArray['foreign_id'] = $pid;
        $patientName = trim(($patientArray['fname'] ?? '') . ' ' . ($patientArray['lname'] ?? ''));
    }
}
$MAX_RECORDS = 25;
$searchparm = trim($_REQUEST['searchparm'] ?? '');
$searchResult = [];
if (!empty($searchparm)) {
    $fuzzySearchName = new StringSearchField("name", [$searchparm], SearchModifier::CONTAINS);
    $searchArray['name'] = $fuzzySearchName;
    $documentService = new DocumentService();
    // we throw a warning if we do more than a 100
    $processingResult = $documentService->search($searchArray, true, ['order' => '`date` DESC', 'limit' => $MAX_RECORDS + 1]);
    if ($processingResult->hasData()) {
        $searchResult = $processingResult->getData();
    }
} else {
    $documentService = new DocumentService();
    $processingResult = $documentService->search($searchArray, true, ['order' => '`date` DESC', 'limit' => $MAX_RECORDS + 1]);
    if ($processingResult->hasData()) {
        $searchResult = $processingResult->getData();
    }
}

// now we have to filter by our permissions for the user, slow process and we can optimize it later, for now we will
// brute force this. Note this does O(2n) mysql queries for each document record
$result = [];
foreach ($searchResult as $docResult) {
    $document = new \Document($docResult['id']);
    if ($document->can_access()) {
        $result[] = $docResult;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['common', 'opener']); ?>
    <title><?php echo htmlspecialchars(xl('Document Finder'), ENT_NOQUOTES); ?></title>

    <style>
        form {
            padding: 0;
            margin: 0;
        }
        #searchCriteria {
            text-align: center;
            width: 100%;
            font-weight: bold;
            padding: 3px;
        }
        #searchResultsHeader {
            width: 100%;
            border-collapse: collapse;
        }
        #searchResults {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
            overflow: auto;
        }

        #searchResults tr {
            cursor: hand;
            cursor: pointer;
        }
        #searchResults td {
            /*font-size: 0.7em;*/
            border-bottom: 1px solid var(--light);
        }
        /* for search results or 'searching' notification */
        #searchstatus {
            font-weight: bold;
            font-style: italic;
            color: var(--black);
            text-align: center;
        }
        #searchspinner {
            display: inline;
            visibility: hidden;
        }

        /* highlight for the mouse-over */
        .oneresult:hover {
            background-color: #336699;
            color: var(--white);
        }
    </style>
</head>

<body class="body_top">
<div class="container-responsive">
    <div id="searchCriteria" class="bg-light p-2 pt-3">
        <form method='post' name='theform' id="theform" action='document_select.php'>
            <div class="form-row">
                <?php if (!empty($pid)) : ?>
                <input type="hidden" name="pid" value="<?php echo attr($pid); ?>" />
                <p>
                    <?php echo xlt("Showing documents for patient"); ?>: <?php echo text($patientName); ?>
                </p>
                <?php endif; ?>
            </div>
            <div class="form-row">
                <label for="searchby" class="col-form-label col-form-label-sm col"><?php echo htmlspecialchars(xl('Search by name:'), ENT_NOQUOTES); ?></label>
                <input type='text' class="form-control form-control-sm col" id='searchparm' name='searchparm' size='12'
                       value='<?php echo attr($_REQUEST['searchparm'] ?? ''); ?>'
                       title='<?php echo xla('Any part of the document name'); ?>' />
                <div class="col">
                    <input class='btn btn-primary btn-sm' type='submit' id="submitbtn" value='<?php echo htmlspecialchars(xl('Search'), ENT_QUOTES); ?>' />
                    <div id="searchspinner"><img src="<?php echo $GLOBALS['webroot'] ?>/interface/pic/ajax-loader.gif" /></div>
                </div>
            </div>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
        </form>
    </div>

    <?php if (! isset($_REQUEST['searchparm'])) : ?>
        <div id="searchstatus"><?php echo htmlspecialchars(xl('Enter your search criteria above'), ENT_NOQUOTES); ?></div>
    <?php elseif (! is_countable($result)) : ?>
        <div id="searchstatus" class="alert alert-danger rounded-0"><?php echo htmlspecialchars(xl('No records found. Please expand your search criteria.'), ENT_NOQUOTES); ?>
        </div>
    <?php elseif (count($result) >= $MAX_RECORDS) : ?>
        <div id="searchstatus" class="alert alert-danger rounded-0"><?php echo htmlspecialchars(xl('More records found than could be displayed. Please narrow your search criteria.'), ENT_NOQUOTES); ?></div>
    <?php elseif (count($result) < $MAX_RECORDS) : ?>
        <div id="searchstatus" class="alert alert-success rounded-0"><?php echo htmlspecialchars(count($result), ENT_NOQUOTES); ?> <?php echo htmlspecialchars(xl('records found.'), ENT_NOQUOTES); ?></div>
    <?php endif; ?>

    <?php if (isset($result)) : ?>
        <table class="table table-sm">
            <thead id="searchResultsHeader" class="head">
            <tr>
                <th class="srName"><?php echo htmlspecialchars(xl('Name'), ENT_NOQUOTES); ?></th>
                <th class="srCategory"><?php echo htmlspecialchars(xl('Category'), ENT_NOQUOTES); ?></th> <!-- (CHEMED) Search by phone number -->
                <th class="srDate"><?php echo htmlspecialchars(xl('Date'), ENT_NOQUOTES); ?></th>
                <th class="srID"><?php echo htmlspecialchars(xl('ID'), ENT_NOQUOTES); ?></th>
            </tr>
            </thead>
            <tbody id="searchResults">
            <?php
            if (is_countable($result)) {
                foreach ($result as $iter) {
                    $name   = $iter['name'];
                    $category = $iter['category_name'];
                    $date = $iter['date'];
                    $id = $iter['id'];

                    // If billing note exists, then it gets special coloring and an extra line of output
                    // in the 'name' column.
                    $trClass = "oneresult";

                    echo " <tr class='" . $trClass . "' id='" . attr($id) . "' data-name='" . attr($name)
                        . "' data-category=" . attr($category) . "' data-date='" . attr($date) . "'>";
                    echo "  <td class='srName'>" . text($name) . "</td>\n";
                    echo "  <td class='srCategory'>" . text($category) . "</td>\n";
                    echo "  <td class='srDate'>" . text($date) . "</td>\n";
                    echo "  <td class='srID'>" . text($id) . "</td>\n";
                    echo " </tr>";
                }
            }
            ?>
            </tbody>
        </table>

    <?php endif; ?>

    <script>

        // jQuery stuff to make the page a little easier to use

        $(function () {
            $("#searchparm").focus();
            $(".oneresult").click(function() { selectDocumentEventHandler(this); });
            //ViSolve
            $(".noresult").click(function () { SubmitForm(this);});

            //$(".event").dblclick(function() { EditEvent(this); });
            $("#theform").submit(function() { SubmitForm(this); });
        });

        function selectDocument(did, name, category, date) {
            if (opener.closed || ! opener.setSelectedDocument)
                alert(<?php echo xlj('The destination form was closed; I cannot act on your selection.'); ?>);
            else
                opener.setSelectedDocument(did, name, category, date);
            dlgclose();
            return false;
        }

        // show the 'searching...' status and submit the form
        var SubmitForm = function(eObj) {
            $("#submitbtn").css("disabled", "true");
            $("#searchspinner").css("visibility", "visible");
            return true;
        }


        // another way to select a patient from the list of results
        // parts[] ==>  0=PID, 1=LName, 2=FName, 3=DOB
        var selectDocumentEventHandler = function (eObj) {
            objID = eObj.id;
            let docId = eObj.id;
            let category = eObj.dataset['category'] || "";
            let name = eObj.dataset['name'] || "";
            let date = eObj.dataset['date'] || "";
            return selectDocument(docId, name, category, date);
        }

    </script>

</div>
</body>
</html>
