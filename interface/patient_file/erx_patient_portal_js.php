//erx account status and patient portal username generator popup modals
$(document).ready(function(){
    $(".iframe1").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 350, 300, '', '', {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
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
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });
});
