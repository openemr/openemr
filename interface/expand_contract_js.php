<?php
  /**
 * expand contract jquery script
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
// ensure that $user_settings_php_path, $arr_files_php variables are set in the script calling this script
?>
$( document ).ready(function() {
    $('.expand_contract').click(function() {
        var elementTitle = $(this).prop('title');
        var contractTitle = '<?php echo xla('Click to Contract and set to henceforth open in Centered mode'); ?>';
        var expandTitle = '<?php echo xla('Click to Expand and set to henceforth open in Expanded mode'); ?>';
        var arrFiles = <?php echo json_encode($arr_files_php) ?>;

        if (elementTitle == contractTitle) {
            elementTitle = expandTitle;
            $(this).toggleClass('fa-expand fa-compress');
            $('.expandable').toggleClass('container container-fluid');
            if ($(arrFiles).length) {
                $.each(arrFiles, function (index, value) {
                    $.post( "<?php echo $GLOBALS['webroot'] ?>/library/ajax/user_settings.php",
                        {
                            target: arrFiles[index].trim(),
                            setting: 0,
                            csrf_token_form: "<?php echo attr(collectCsrfToken()); ?>"
                        }
                    );
                });
            }
        } else if (elementTitle == expandTitle) {
            elementTitle = contractTitle;
            $(this).toggleClass('fa-compress fa-expand');
            $('.expandable').toggleClass('container-fluid container');
            if ($(arrFiles).length) {
                $.each(arrFiles, function (index, value) {
                    $.post( "<?php echo $GLOBALS['webroot'] ?>/library/ajax/user_settings.php",
                        {
                            target: arrFiles[index].trim(),
                            setting: 1,
                            csrf_token_form: "<?php echo attr(collectCsrfToken()); ?>"
                        }
                    );
                });
            }
        }
        $(this).prop('title', elementTitle);
    });
});
