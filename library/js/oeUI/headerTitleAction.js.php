<?php
/**
 * Expand Contract State and Show /Hide State js
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
?>
$(document).ready(function () {
    $('.expand_contract').click(function () {
        var elementTitle;
        var expandTitle = <?php echo xlj("Click to Contract and set to henceforth open in Centered mode"); ?>;
        var contractTitle = <?php echo xlj("Click to Expand and set to henceforth open in Expanded mode"); ?>;
        var arrFiles = <?php echo json_encode($arrOeUiSettings['expandable_files']) ?>;

        if ($(this).is('.oe-expand')) {
            elementTitle = expandTitle;
            $(this).toggleClass('fa-expand fa-compress');
            $(this).toggleClass('oe-expand oe-center');
            $('#container_div').toggleClass('container container-fluid');
            if ($(arrFiles).length) {
                $.each(arrFiles, function (index, value) {
                
                    $.post(
                        "<?php echo $GLOBALS['webroot'] ?>/library/ajax/user_settings.php",
                        {
                            target: arrFiles[index].trim(),
                            setting: 1,
                            csrf_token_form: <?php echo js_escape(collectCsrfToken());?>
                        }
                    );
                });
            }
        } else if ($(this).is('.oe-center')) {
            elementTitle = contractTitle;
            $(this).toggleClass('fa-compress fa-expand');
            $(this).toggleClass('oe-center oe-expand');
            $('#container_div').toggleClass('container-fluid container');
            if ($(arrFiles).length) {
                $.each(arrFiles, function (index, value) {
                    $.post(
                        "<?php echo $GLOBALS['webroot'] ?>/library/ajax/user_settings.php",
                        {
                            target: arrFiles[index].trim(),
                            setting: 0,
                            csrf_token_form: <?php echo js_escape(collectCsrfToken());?>
                        }
                    );
                });
            }
        }
        $(this).prop('title', elementTitle);
    });
    
    $('#show_hide').click(function () {
        var elementTitle = '';
        <?php
        if ($action == 'search') {
            echo "var showTitle = " .  xlj('Click to show search') . ";\r\n";
            echo "var hideTitle = " . xlj('Click to hide search') . ";\r\n";
        } elseif ($action == 'reveal' || $action == 'conceal') {
            echo "var hideTitle = " .  xlj('Click to Hide') . "\r\n;";
            echo "var showTitle = " . xlj('Click to Show') . "\r\n;";
        }
        ?>
        
        $('.hideaway').toggle(500);
        <?php
        if ($action == 'search') {
            echo "$(this).toggleClass('fa-search-plus fa-search-minus'); \r\n";
        } elseif ($action == 'reveal') {
            echo "$(this).toggleClass('fa-eye fa-eye-slash'); \r\n";
        } elseif ($action == 'conceal') {
            echo "$(this).toggleClass('fa-eye-slash fa-eye'); \r\n";
        }
        ?>
        if ($(this).is('.fa-eye') || $(this).is('.fa-search-plus')) {
            elementTitle = showTitle;
        } else if ($(this).is('.fa-eye-slash') || $(this).is('.fa-search-minus')) {
            elementTitle = hideTitle;
        }
        $(this).prop('title', elementTitle);
    });
});
