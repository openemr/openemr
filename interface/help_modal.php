<?php
/**
 * Help modal.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('globals.php');

use OpenEMR\Core\Header;

Header::setupHeader(['jquery-ui', 'jquery-ui-base', 'no_jquery', 'no_bootstrap', 'no_fontawesome', 'no_main-theme', 'no_textformat', 'no_dialog' ]);
?>
 <br>
<?php
$close =  xla("Close");
$print = xla("Print");

$help_modal = <<<HELP
<div class="row">
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content  oe-modal-content" style="height:700px">
                <div class="modal-header clearfix">
                    <button type="button" class="close" data-dismiss="modal" aria-label=$close>
                    <span aria-hidden="true" style="color:#000000; font-size:1.5em;">×</span></button>
                </div>
                <div class="modal-body" style="height:80%;">
                    <iframe src="" id="targetiframe" style="height:100%; width:100%; overflow-x: hidden; border:none"
                    allowtransparency="true"></iframe>  
                </div>
                <div class="modal-footer" style="margin-top:0px;">
                   <button class="btn btn-link btn-cancel oe-pull-away" data-dismiss="modal" type="button">$close</button>
                   <!--<button class="btn btn-default btn-print oe-pull-away" data-dismiss="modal" id="print-help-href" type="button">$print</button>-->
                </div>
            </div>
        </div>
    </div>
</div>
HELP;

echo $help_modal;
?>
<script>
    var helpFilePath = '<?php echo "$webroot/Documentation/help_files/"?>';
    $(function() {
        $('#help-href').click (function(){
            document.getElementById('targetiframe').src = helpFilePath + helpFile;
        })
    });
    <?php //print needs work on ie, edge browsers?>
    $(function() {
        $('#print-help-href').click (function(){
            $("#targetiframe").get(0).contentWindow.print();
        })
    });
    // Jquery draggable
    $('.modal-dialog').draggable({
            handle: ".modal-header, .modal-footer"
    });
   $( ".modal-content" ).resizable({
        aspectRatio: true,
        minHeight: 300,
        minWidth: 300
    });
</script>