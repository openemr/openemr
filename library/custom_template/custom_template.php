<?php
// +-----------------------------------------------------------------------------+
// Copyright (C) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Jacob T Paul <jacob@zhservices.com>
//
// +------------------------------------------------------------------------------+
//
// Major code refactoring by MD Support <mdsupport@users.sourceforge.net>

require_once("../../interface/globals.php");
require_once("$srcdir/lists.inc");

use OpenEMR\Core\Header;

// mdsupport : html code helpers

$jsTxt = array();
$ixTxt = 0;

function seloptCode($strSelOpt, $strSelDisp)
{
    if ($strSelDisp) {
        return sprintf('<option value="%s">%s</option>%s',
            htmlspecialchars($strSelOpt, ENT_QUOTES), htmlspecialchars($strSelDisp, ENT_QUOTES), PHP_EOL
        );
    }
}
function listitemCode($strDisp, $strTxt, $enDisp=TRUE, $enTxt=TRUE)
{
    global $jsTxt, $ixTxt;

    if (empty($strDisp) || empty($strTxt)) return '';
    
    if ($enDisp) {
        $strDisp = htmlspecialchars($strDisp, ENT_QUOTES);
    }

    if ($enTxt) {
        $strTxt = htmlspecialchars($strTxt, ENT_QUOTES);
    }

    $jsTxt['i'.$ixTxt] = $strTxt;

    return '<li><a class="ck-ins" href="#" data-txt="'.$strTxt.'">'.$strDisp.'</a></li>'.PHP_EOL;
}
function insbtnCode($vIns, $strInsType, $strBtnTitle, $strBtnHtm)
{
    if (is_array($vIns)) {
        $strIns = $vIns['ins'];
        $strInsType = $vIns['insType'];
        $strBtnTitle = $vIns['title'];
        $strBtnHtm = $vIns['disp'];
    } else {
        $strIns = $vIns;
    }
    return sprintf(
        '<button class="ck-ins%s" href="#" data-%s="%s" title="%s">
            %s
        </button>%s',
        ($strInsType=='htm' ? ' ck-htm':''), $strInsType, $strIns,
        htmlspecialchars(xl($strBtnTitle), ENT_QUOTES), $strBtnHtm, PHP_EOL);
}

$contextName = $_REQUEST['contextName'];
$type = $_REQUEST['type'];
$recContext = sqlQuery("SELECT * FROM customlists WHERE cl_list_type=2 AND cl_list_item_long=?", array($contextName));
if (empty($recContext['cl_list_item_long'])) {
    // Received invalid context, exit now
    printf(
'<html>
<body>
    <strong>%s</strong>
</body>
</html>',
        htmlspecialchars($contextName. ': '. xl('NO SUCH CONTEXT NAME'), ENT_QUOTES)
    );
    exit;
}

$rs = sqlStatement(
    "SELECT *
        FROM template_users AS tu LEFT OUTER JOIN customlists AS c ON tu.tu_template_id=c.cl_list_slno
        WHERE tu.tu_user_id=? AND c.cl_list_type=3 AND cl_list_id=? AND cl_deleted=0 ORDER BY c.cl_list_item_long",
    array($_SESSION['authId'],$recContext['cl_list_id']));
$optCats = seloptCode('', xl('Error')); // If seen, Select2 placeholder did not work
while ($rec = sqlFetchArray($rs)) {
    $optCats .= seloptCode($rec['cl_list_slno'], xl($rec['cl_list_item_long']));
}

// Gather all needed elements in this array
$aDisp = array();

if (!empty($pid)) {
    $recPt = sqlQuery(
        "SELECT p.*, CONCAT(p.lname,', ',p.fname) lfname, TIMESTAMPDIFF(YEAR, p.DOB, NOW()) yrs,
        IF(ISNULL(p.providerID), NULL, CONCAT(u.lname,',',u.fname)) pcp
        FROM patient_data p LEFT OUTER JOIN users u ON u.id=p.providerID
        WHERE pid=?",
        array($pid));
    // Field mapping
    $recMap = array(
        'lfname' => 'Full',
        'fname' => 'First',
        'lname' => 'Last',
        'phone_home' => 'Phone',
        'yrs' => 'Years',
        'DOB' => 'DoB',
        'pcp' => 'PCP',
    );
    $itmsHtm = '';
    foreach ($recMap as $fldMap => $lblMap) {
        $itmDisp = sprintf('%s: %s', htmlspecialchars(xl($lblMap), ENT_QUOTES), $recPt[$fldMap]);
        $itmsHtm .= listitemCode($itmDisp, $recPt[$fldMap]);
    }
    $aDisp['ptData'] = array(
        'title' => htmlspecialchars($recPt['lfname'], ENT_QUOTES),
        'hdrId' => 'hdrptData',
        'itmsId' => 'itmsptData',
        'itmsHtm' => $itmsHtm,
    );

    foreach ($ISSUE_TYPES as $issType => $issTypeDesc) {
        $rs = sqlStatement(
            'SELECT title, IF(diagnosis="","",CONCAT(" [",diagnosis,"]")) codes
            FROM lists
            WHERE pid=? AND type=? AND (IFNULL(enddate,0) = 0)
            ORDER BY title',
            array($pid, $issType)
        );
        $itmsHtm = '';
        while ($rec = sqlFetchArray($rs)) {
            $itmsHtm .= listitemCode($rec['title'], ($rec['title'].$rec['codes']));
        }
        
        if (strlen($itmsHtm) > 0) {
            $aDisp['iss-'.$issType] = array(
                'title' => htmlspecialchars(xl($issTypeDesc[0]), ENT_QUOTES),
                'hdrId' => 'hdr'.$issType,
                'itmsId' => 'itms'.$issType,
                'itmsHtm' => $itmsHtm,
            );
        }
    }
}

// Build toolbar
$tbDisp = '';
// Add legacy buttons - Should be moved to database and be localizable
$aBtns = array(
    'b0' => array(
        'ins' => "<br>",
        'insType' => "htm",
        'title' => "New line",
        'disp' => '<img border=0 src="../../images/enter.gif">'
    ), 
    'b1' => array(
        'ins' => "? ",
        'insType' => "txt",
        'title' => "Question Mark",
        'disp' => '<i class="fa fa-question"></i>'
    ),
    'b2' => array(
        'ins' => "<p>...</p>",
        'insType' => "htm",
        'title' => "New Paragraph",
        'disp' => '<i class="fa fa-paragraph"></i>'
    ),
    'b3' => array(
        'ins' => " ",
        'insType' => "txt",
        'title' => "Blank space",
        'disp' => htmlspecialchars(xl('SPACE'), ENT_QUOTES)
    ),
);

$rs = sqlStatement(
    "SELECT * FROM template_users AS tu 
    LEFT OUTER JOIN customlists AS cl ON cl.cl_list_slno=tu.tu_template_id
    WHERE tu.tu_user_id=? AND cl.cl_list_type=6 AND cl.cl_deleted=0 
    ORDER BY cl.cl_order",
    array($_SESSION['authId'])
);
$ixTxt = count($aBtns);
while ($rec=sqlFetchArray($rs)) {
    $aBtns['b'.$ixTxt] = array(
        'ins' => $rec['cl_list_item_short'],
        'insType' => "txt",
        'title' => htmlspecialchars(xl($rec['cl_list_item_long']), ENT_QUOTES),
        'disp' => ucfirst(htmlspecialchars(xl($rec['cl_list_item_long']), ENT_QUOTES))
    );
    $ixTxt++;
}
foreach ($aBtns as $key => $aBtn) {
    $tbDisp .= insbtnCode($aBtn);
}
?>
<html lang="en">
<head>
    <?php Header::setupHeader(['common', 'opener', 'select2']); ?>
</head>

<body class="body_top">
  <div class='container-fluid'>
    <div class='row'>
      <div id='title-block' class='m-0 p-0'>
        <div class="h4 mr-2">

          <?php echo strtoupper(htmlspecialchars(xl($recContext['cl_list_item_long']), ENT_QUOTES));?>

          <a class="btn btn-secondary btn-sm m-0 p-0" href="#" role="button" onclick="return SelectToSave('<?php echo $type;?>')">
             <?php echo htmlspecialchars(xl('Update'), ENT_QUOTES);?>
          </a>

          <div class="dropdown float-right pl-2 m-0">
              <button class="btn btn-light dropdown-toggle btn-sm text-dark" type="button" id="btnSettings" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                 <strong><?php echo htmlspecialchars(xl('Personalize'), ENT_QUOTES);?></strong>
              </button>
              <div class="dropdown-menu" aria-labelledby="btnSettings">
                  <a id="personalize_link" class="iframe_medium dropdown-item" 
                      href="personalize.php?list_id=<?php echo $recContext['cl_list_id'];?>">
                          <?php echo htmlspecialchars(xl('Categories and contexts'), ENT_QUOTES);?>
                  </a>
                  <a id="custombutton" class="iframe_medium dropdown-item" href="add_custombutton.php" 
                      title="<?php echo htmlspecialchars(xl('Add Buttons for Special Chars,Texts to be Displayed on Top of the Editor for inclusion to the text on a Click'), ENT_QUOTES);?>">
                          <?php echo htmlspecialchars(xl('Shortcut Buttons'), ENT_QUOTES);?>
                  </a>
              </div>
          </div>

        </div>
      </div>
    </div>
    <div class='row'>
      <div class='col-3'>
        <div id="accordion">

            <div class="card">
                <div id="hdrCatDD">
                    <form class="m-0">
                        <div class="card-header">
                            <select name="selCat" id="selCat">
                                <?php echo $optCats ?>
                            </select>
                        </div>
                    </form>
                </div>

                <div id="itmsCatDD" class="collapse show"
                    aria-labelledby="hdrCatDD" data-parent="#accordion">
                    <div class="card-body">
                        <ul id="template_sentence">
                            <?php echo xl('No templates set') ?>
                        </ul>
                    </div>
                </div>
            </div>

            <?php
            foreach ($aDisp as $keyDisp => $aItm) {
            ?>
            <div class="card">
                <div class="card-header" id="<?php echo $aItm['hdrId'] ?>">
                    <h5 class="m-0">
                        <button class="btn btn-link collapsed" data-toggle="collapse"
                            data-target="#<?php echo $aItm['itmsId'] ?>" aria-expanded="false"
                            aria-controls="<?php echo $aItm['itmsId'] ?>">
                            <?php echo $aItm['title']; ?>
                        </button>
                    </h5>
                </div>
                <div id="<?php echo $aItm['itmsId'] ?>" class="collapse"
                    aria-labelledby="<?php echo $aItm['hdrId'] ?>" data-parent="#accordion">
                    <div class="card-body">
                        <ul>
                            <?php echo $aItm['itmsHtm']; ?>
                        </ul>
                    
                    </div>
                </div>
            </div>
            <?php } ?>

        </div>

      </div>

      <div class='col-9'>
          <div class="row">
              <div id="share" style="display:none"></div>
              <div class="btn-group btn-group-sm" role="group">
                  <?php echo $tbDisp; ?>
              </div>
          </div>
          <div class="row">
            <input type="hidden" name="list_id" id="list_id" value="<?php echo $recContext['cl_list_id'];?>">
            <textarea class="ckeditor" cols="100" id="textarea1" name="textarea1" rows="80"></textarea>
          </div>
      </div>
    </div>
  </div>

<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/ckeditor-4-7-0/ckeditor.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/ajax_functions_writer.js"></script>

<script type="text/javascript">
    <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

    // Bind editor
    edit('<?php echo $type;?>');
</script>

<script language="JavaScript" type="text/javascript">

    CKEDITOR.config.customConfig = top.webroot_url + '/library/js/nncustom_config.js';

    function refreshme() {
        top.restoreSession();
        document.location.reload();
    }

    $(document).ready(function () {

        // tabbify();

        $("#accordion .card-header, #accordion .card-body").addClass("p-0");

        $("#accordion .card-body > ul").addClass("list-group");
        $(".list-group > li").addClass("list-group-item py-1 px-1 text-truncate");

        // mdsupport - removed copy+paste for the common function
        $(".iframe_small").on('click', function (e) {
            dlgOpenCommon(e, $(this), 330, 120);
        });

        $(".iframe_medium").on('click', function (e) {
            dlgOpenCommon(e, $(this), 725, 500);
        });

        $(".iframe_abvmedium").on('click', function (e) {
            dlgOpenCommon(e, $(this), 725, 500);
        });

        $("#selCat").select2({
            width: '100%',
            dropdownAutoWidth: true,
            placeholder: "<?php echo htmlspecialchars(xl('Select category'), ENT_QUOTES) ?>"
        });

    });

    function dlgOpenCommon(e, src, dlgW, dlgH) {
        e.preventDefault();
        e.stopPropagation();
        dlgopen('', '', dlgW, dlgH, '', '', {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
            ],
            onClosed: 'refreshme',
            type: 'iframe',
            url: src.attr('href')
        });
    }

    $("#selCat")
    .on("select2:opening", function() {
        $("#itmsCatDD").collapse("show");
    })
    .on("change", function() {
        TemplateSentence($(this).val());
    });

    // Until other components are modified, repair text for modern browsers
    $("#template_sentence").on("DOMSubtreeModified", function() {
        $("#template_sentence > li")
        .addClass("list-group-item py-1 px-1 text-truncate")
        .on('mouseover', function() {
            $(this).delay(300).removeClass('text-truncate');
        })
        .on('mouseout', function() {
            $(this).delay(300).addClass('text-truncate');
        });
    });

    $('button.ck-ins').addClass('btn btn-light text-dark');

    $('body').on('click', '.ck-ins', function() {
        top.restoreSession();
        if ($(this).hasClass('ck-htm')) {
            CKEDITOR.instances.textarea1.insertHtml($(this).data('htm'));
        } else {
            CKEDITOR.instances.textarea1.insertText($(this).data('txt'));
        }
    }); 
    // mdsupport - TBD migrate to bs if essential
    /*
            $(function () {
            $("#menu5 div").sortable({
                opacity: 0.3,
                cursor: 'move',
                update: function () {
                    var order = $(this).sortable("serialize") + '&action=updateRecordsListings';
                    $.post("updateDB.php", order);
                }
            });
        });
    */

</script>
</body>
</html>
