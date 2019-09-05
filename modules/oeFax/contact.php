<?php
/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
namespace Modules\oeFax\Controller;

require_once(__DIR__ . "/../../interface/globals.php");

use OpenEMR\Core\Header;

// kick off app endpoints controller
$clientApp = AppDispatch::getApiService();
$logged_in = $clientApp->authenticate();
$the_file = $clientApp->getRequest('file');
$isContent = $clientApp->getRequest('isContent');
$isDoc = $clientApp->getRequest('isDocuments');
$isQueue = $clientApp->getRequest('isQueue');
$file_name = pathinfo($the_file, PATHINFO_BASENAME);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Contact') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php Header::setupHeader();
    echo "<script>var pid=" . js_escape($pid) . "</script>";
    ?>
    <script>
        $(function () {
            // when the form is submitted
            $('#contact-form').on('submit', function (e) {
                if (!e.isDefaultPrevented()) {
                    let wait = '<i class="fa fa-cog fa-spin fa-4x"></i>';
                    let url = 'sendFax';
                    // POST values in the background the script URL
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: $(this).serialize(),
                        success: function (data) {
                            var err = (data.search(/Exception/) !== -1 ? 1 : 0);
                            if (!err) {
                                err = (data.search(/Error:/) !== -1) ? 1 : 0;
                            }
                            // we recieve the type of the message: success x danger and apply it to the
                            var messageAlert = 'alert-' + (err !== 0 ? 'danger' : 'success');
                            var messageText = data;

                            // let's compose alert box HTML
                            var alertBox = '<div class="alert ' + messageAlert + ' alert-dismissable">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + messageText + '</div>';

                            // If we have messageAlert and messageText
                            if (messageAlert && messageText) {
                                // inject the alert to messages div in our form
                                $('#contact-form').find('.messages').html(alertBox);
                                setTimeout(function () {
                                    if (!err) {
                                        // empty the form
                                        $('#contact-form')[0].reset();
                                        $(".alert").alert('close');
                                        dlgclose();
                                    }
                                    $(".alert").alert('close');
                                    // reload so OAuth dialog can do it's annoying thing.
                                    // backend prepared for this.
                                    location.reload();
                                }, 4500);
                            }
                        }
                    });
                    return false;
                }
            })
        });

        function contactCallBack(contact) {
            let actionUrl = 'getUser';
            return $.post(actionUrl, {'uid': contact}, function (d, s) {
                //$("#wait").remove()
            }, 'json').done(function (data) {
                $("#form_name").val(data[0]);
                $("#form_lastname").val(data[1]);
                $("#form_phone").val(data[2]);
            });
        }

        var getContactBook = function (e, rtnpid) {
            e.preventDefault();
            let btnClose = <?php echo xlj("Cancel"); ?>;
            dlgopen('', '', 'modal-lg', 500, '', '', {
                buttons: [
                    {text: btnClose, close: true, style: 'primary  btn-sm'}
                ],
                url: top.webroot_url + '/interface/usergroup/addrbook_list.php?popup=2',
                dialogId: 'fax'
            });
        };
    </script>
    <style>
        .panel-body {
            word-wrap: break-word;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="container">
        <form class="form" id="contact-form" method="post" action="contact.php" role="form">
            <input type="hidden" id="form_file" name="file" value='<?php echo attr($the_file) ?>'>
            <input type="hidden" id="form_isContent" name="isContent" value='<?php echo attr($isContent); ?>'>
            <input type="hidden" id="form_isDocuments" name="isDocuments" value='<?php echo attr($isDoc) ?>'>
            <input type="hidden" id="form_isDocuments" name="isQueue" value='<?php echo attr($isQueue) ?>'>
            <div class="messages"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="form_name"><?php echo xlt('Firstname') ?> *</label>
                        <input id="form_name" type="text" name="name" class="form-control">
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="form_lastname"><?php echo xlt('Lastname') ?> *</label>
                        <input id="form_lastname" type="text" name="surname" class="form-control">
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="form_email"><?php echo xlt('Email') ?></label>
                        <input id="form_email" type="email" name="email" class="form-control"
                            placeholder="<?php echo xla('Not required for fax') ?>">
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="form_phone"><?php echo xlt('Fax Phone') ?> *</label>
                        <input id="form_phone" type="tel" name="phone" class="form-control" required="required">
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="form_message"><?php echo xlt('Message') ?></label>
                        <textarea id="form_message" name="comments" class="form-control" placeholder="
                            <?php echo xla('Comment for cover sheet') ?>" rows="4"></textarea>
                        <div class="help-block with-errors"></div>
                    </div>
                    <div>
                        <span class="text-center"><strong><?php echo xlt('Sending File') . ': ' ?></strong><?php echo text($file_name) ?></span>
                    </div>
                    <div class="pull-right">
                        <button type="button" class="btn btn-primary btn-sm" onclick="getContactBook(event, pid)" value="Contacts"><?php echo xlt('Contacts') ?></button>
                        <button type="submit" class="btn btn-success btn-sm" value=""><?php echo xlt('Send Fax') ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
