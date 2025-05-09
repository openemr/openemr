<?php

/**
 * Help modal.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018-2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('globals.php');

?>
 <br />
<?php
$close =  xla("Close");
$print = xla("Print");

$help_modal = <<<HELP
<div class="row">
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content oe-modal-content" style="height: 700px">
                <div class="modal-header clearfix">
                    <button type="button" class="close" data-dismiss="modal" aria-label=$close>
                    <span aria-hidden="true" class="text-body" style="font-size: 1.5rem;">&times;</span></button>
                </div>
                <div class="modal-body" style="height:80%;">
                    <iframe src="" id="targetiframe" class="border-0 h-100 w-100" style="overflow-x: hidden;" allowtransparency="true"></iframe>  
                </div>
                <div class="modal-footer mt-0">
                   <button class="btn btn-link btn-cancel oe-pull-away" data-dismiss="modal" type="button">$close</button>
                   <!--<button class="btn btn-secondary btn-print oe-pull-away" data-dismiss="modal" id="print-help-href" type="button">$print</button>-->
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
    $(function () {
        $('#help-href').click (function(){
            document.getElementById('targetiframe').src = helpFilePath + helpFile;
        })
    });
    <?php //print needs work on ie, edge browsers?>
    $(function () {
        $('#print-help-href').click (function(){
            $("#targetiframe").get(0).contentWindow.print();
        })
    });

    // Jquery draggable
    $(".modal-dialog").addClass('drag-action');
    $(".modal-content").addClass('resize-action');
</script>
