<?php

/**
 * interface/super/rules/controllers/edit/helper/common.php
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
<!--

General Helpers

-->

<!-- -->
<!-- -->
<!-- -->
<?php function render_select($args)
{
    ?>
<select class="form-control" data-grp-tgt="<?php echo attr($args['target']); ?>" type="dropdown" name="<?php echo attr($args['name']); ?>" id="<?php echo attr($args['id']); ?>">

    <!-- default option -->
    <option id="" value="">--<?php echo xlt('Select'); ?>--</option>

    <!-- iterate over other options -->
    <?php foreach ($args['options'] as $option) { ?>
    <option id="<?php echo attr($option['id']); ?>"
            value="<?php echo attr($option['id']); ?>"
            <?php echo $args['value'] == $option['id'] ? "SELECTED" : "" ?>>
        <?php echo xlt($option['label']); ?>
    </option>
    <?php } ?>

</select>
<?php } ?>

<!-- -->
<!-- -->
<!-- -->
<?php function textfield_row($args)
{
    ?>
<p class="row">
    <span class="left_col colhead req" data-field="<?php echo attr($args['name']); ?>"><?php echo text($args['title']); ?></span>
    <span class="end_col">
        <input id="<?php echo $args['id'] ? attr($args['id']) : ""?>" data-grp-tgt="<?php echo attr($args['target']); ?>" class="form-control field <?php echo attr($args['class']); ?>" type="text" name="<?php echo attr($args['name']); ?>" value="<?php echo attr($args['value']);?>" />
    </span>
</p>
<?php } ?>

<!--

Compound Helpers

-->

<!-- -->
<!-- -->
<!-- -->
<?php function common_fields($args)
{
    ?>
    <?php $criteria = $args['criteria'];  ?>
    <p class="row">
        <span class="left_col colhead req" data-field="fld_optional"><?php echo xlt('Optional'); ?></span>
        <span class="end_col">
            <input id="fld_optional" type="radio" name="fld_optional" class="field" value="yes"
                    <?php echo $criteria->optional ? "CHECKED" : ""?>> <?php echo xlt('Yes'); ?>
            <input id="fld_optional" type="radio" name="fld_optional" class="field" value="no"
                    <?php echo !$criteria->optional ? "CHECKED" : ""?>> <?php echo xlt('No'); ?>
        </span>
    </p>

    <p class="row">
        <span class="left_col colhead req" data-field="fld_inclusion"><?php echo xlt('Inclusion'); ?></span>
        <span class="end_col">
            <input id="fld_inclusion" type="radio" name="fld_inclusion" class="field" value="yes"
                    <?php echo $criteria->inclusion ? "CHECKED" : ""?>> <?php echo xlt('Yes'); ?>
            <input id="fld_inclusion" type="radio" name="fld_inclusion" class="field" value="no"
                    <?php echo !$criteria->inclusion ? "CHECKED" : ""?>> <?php echo xlt('No'); ?>
        </span>
    </p>

    <?php if ($criteria->interval && $criteria->intervalType) { ?>
    <p class="row">
        <span class="left_col colhead req" data-field="fld_target_interval"><?php echo xlt('Interval'); ?></span>
        <span class="end_col">
            <input data-grp-tgt="flt_target_interval" class="form-control field short" type="text" name="fld_target_interval" value="<?php echo xlt($criteria->interval); ?>" />

            <?php echo timeunit_select(array( "context" => "rule_target_intervals", "target" => "fld_target_interval_", "name" => "fld_target_interval_type", "value" => $criteria->intervalType )); ?>
        </span>
    </p>
    <?php } ?>
<?php } ?>

<!--                  -->
<!-- render time unit -->
<!--                  -->
<?php function timeunit_select($args)
{
    require_once($GLOBALS["srcdir"] . "/options.inc.php");

    return generate_select_list(
        $args['name'],
        $args['context'],
        $args['value']->code,
        $args['name'],
        '',
        '',
        '',
        $args['id'],
        array( "data-grp-tgt" => $args['target'] )
    );
} ?>
