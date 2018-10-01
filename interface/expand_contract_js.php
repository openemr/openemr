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
// ensure that $user_settings_php_path, $current_filename, $arr_files variables are set in the script calling this script
// If no linked files that need to maintain current state  $arr_files should be an empty array
?>
$( document ).ready(function() {
    // var contractTitle = '<?php echo xla('Click to Contract and set to henceforth open in Centered mode'); ?>';
    // var expandTitle = '<?php echo xla('Click to Expand and set to henceforth open in Expanded mode'); ?>';
    $('.expand_contract').click(function() {
        var elementTitle = $(this).prop('title');
        var contractTitle = '<?php echo xla('Click to Contract and set to henceforth open in Centered mode'); ?>';
        var expandTitle = '<?php echo xla('Click to Expand and set to henceforth open in Expanded mode'); ?>';
        var arrFiles = [];
        <?php
        if ($arr_files) {?>
            arrFiles = [<?php echo text($arr_files);?>];
        <?php
        } ?>
        //alert(contractTitle + " " + expandTitle);
        if (elementTitle == contractTitle) {
            elementTitle = expandTitle;
            $(this).toggleClass('fa-expand fa-compress');
            $('.expandable').toggleClass('container container-fluid');
            if ($(arrFiles).length) {
                $.each(arrFiles, function (index, value) {
                  $.post( '<?php echo $user_settings_php_path; ?>', { target: arrFiles[index], setting: 0 });
                });
            } else {
                $.post( '<?php echo $user_settings_php_path; ?>', { target: '<?php echo $current_filename; ?>', setting: 0 });
            }
        } else if (elementTitle == expandTitle) {
            elementTitle = contractTitle;
            $(this).toggleClass('fa-compress fa-expand');
            $('.expandable').toggleClass('container-fluid container');
            if ($(arrFiles).length) {
                $.each(arrFiles, function (index, value) {
                  $.post( '<?php echo $user_settings_php_path; ?>', { target: arrFiles[index], setting: 1 });
                });
            } else {
                $.post( '<?php echo $user_settings_php_path; ?>', { target: '<?php echo $current_filename; ?>', setting: 1 });
            }
        }
        $(this).prop('title', elementTitle);
    });
});
