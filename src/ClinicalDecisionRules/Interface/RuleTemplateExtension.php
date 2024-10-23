<?php

// TODO: @adunsulag at some point this could become a twig extension

namespace OpenEMR\ClinicalDecisionRules\Interface;

class RuleTemplateExtension
{
    public static function render_select($args)
    {
        ?>
        <select class="form-control" data-grp-tgt="<?php echo attr($args['target']); ?>" type="dropdown" name="<?php echo attr($args['name']); ?>" id="<?php echo attr($args['id'] ?? ''); ?>">

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
    <?php }

    public static function textfield_row($args)
    {
        ?>
        <p class="form-row">
            <span class="left_col colhead req" data-field="<?php echo attr($args['name']); ?>"><?php echo text($args['title']); ?></span>
            <span class="end_col">
        <input id="<?php echo $args['id'] ? attr($args['id']) : "" ?>" data-grp-tgt="<?php echo attr($args['target'] ?? ''); ?>" class="form-control field <?php echo attr($args['class'] ?? ''); ?>" type="text" name="<?php echo attr($args['name']); ?>" value="<?php echo attr($args['value']);?>" />
    </span>
            <span class="ml-1"><?php echo $args['render_link'] ?? ""; ?></span>
        </p>
    <?php }

    public static function common_fields($args)
    {
        ?>
        <?php $criteria = $args['criteria'];  ?>
        <p class="form-row">
            <span class="left_col colhead req" data-field="fld_optional"><?php echo xlt('Optional'); ?></span>
            <span class="end_col">
            <input id="fld_optional" type="radio" name="fld_optional" class="field" value="no"
                    <?php echo !$criteria->optional ? "CHECKED" : ""?>> <?php echo xlt('Yes');?>
            <input id="fld_optional" type="radio" name="fld_optional" class="field" value="yes"
                    <?php echo $criteria->optional ? "CHECKED" : ""?>> <?php echo xlt('No'); ?>
        </span>
        </p>

        <p class="form-row">
            <span class="left_col colhead req" data-field="fld_inclusion"><?php echo xlt('Inclusion'); ?></span>
            <span class="end_col">
            <input id="fld_inclusion" type="radio" name="fld_inclusion" class="field" value="yes"
                    <?php echo $criteria->inclusion ? "CHECKED" : ""?>> <?php echo xlt('Yes'); ?>
            <input id="fld_inclusion" type="radio" name="fld_inclusion" class="field" value="no"
                    <?php echo !$criteria->inclusion ? "CHECKED" : ""?>> <?php echo xlt('No'); ?>
        </span>
        </p>

        <?php if ($criteria->interval && $criteria->intervalType) { ?>
        <p class="form-row">
            <span class="left_col colhead req" data-field="fld_target_interval"><?php echo xlt('Interval'); ?></span>
            <span class="end_col">
            <input data-grp-tgt="flt_target_interval" class="form-control field short" type="text" name="fld_target_interval" value="<?php echo xlt($criteria->interval); ?>" />

            <?php echo self::timeunit_select(array( "context" => "rule_target_intervals", "target" => "fld_target_interval_", "name" => "fld_target_interval_type", "value" => $criteria->intervalType )); ?>
        </span>
        </p>
    <?php } ?>
    <?php }

    public static function timeunit_select($args)
    {
        require_once($GLOBALS["srcdir"] . "/options.inc.php");

        return generate_select_list(
            $args['name'],
            $args['context'],
            $args['value']->code ?? null,
            $args['name'],
            '',
            '',
            '',
            $args['id'] ?? '',
            array( "data-grp-tgt" => $args['target'] )
        );
    }

    public static function getLabel($value, $list_id)
    {
        require_once($GLOBALS["srcdir"] . "/options.inc.php");

        // get from list_options
        $result = generate_display_field(array('data_type' => '1','list_id' => $list_id), $value);
        // trap for fa-exclamation-circle used to indicate empty input from layouts options.
        if ($result != '' && stripos($result, 'fa-exclamation-circle') === false) {
            return $result;
        }

        // if not found, default to the passed-in value
        return $value;
    }


    public static function getLayoutLabel($value, $form_id)
    {
        // get from layout_options
        $sql = sqlStatement(
            "SELECT title from layout_options WHERE form_id = ? and field_id = ?",
            array($form_id, $value)
        );
        if (sqlNumRows($sql) > 0) {
            $result = sqlFetchArray($sql);
            return xl($result['title']);
        }

// if not found, default to the passed-in value
        return $value;
    }
}
