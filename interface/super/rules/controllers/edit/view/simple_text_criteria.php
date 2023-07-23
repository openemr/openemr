<?php

/**
 * interface/super/rules/controllers/edit/view/simple_text_criteria.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<p class="form-row">
    <span class="left_col colhead req" data-fld="fld_value"><?php echo text($criteria->getTitle()); ?></span>
    <span class="end_col"><input id="fld_value" type="text" name="fld_value" class="form-control field" value="<?php echo attr($criteria->getRequirements()); ?>"></span>
</p>

<?php //echo textfield_row(array("name" => "fld_value",
      //                         "title" => $criteria->getTitle(),
      //                         "value" =>$criteria->getRequirements() ) ); ?>


<!-- optional/required and inclusion/exclusion fields -->
<?php echo common_fields(array( "criteria" => $criteria)); ?>
