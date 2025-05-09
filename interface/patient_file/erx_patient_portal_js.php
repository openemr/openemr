<?php

 /**
  * erx account status and patient portal username generator popup modals
  *
  * @package   OpenEMR
  * @link      http://www.open-emr.org
  * @author    Ranganath Pathak <pathak@scrs1.org>
  * @author    Brady Miller <brady.g.miller@gmail.com>
  * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
  * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
  * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
  */

?>
//erx account status and patient portal username generator popup modals
$(function () {
    $(".iframe1").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 350, 300, '', '', {
            buttons: [
                {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });
    // for patient portal
    $(".small_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 380, 200, '', '', {
            buttons: [
                {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });
});
