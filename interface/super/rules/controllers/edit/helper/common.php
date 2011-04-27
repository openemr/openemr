<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>
<!--

General Helpers

-->

<!-- -->
<!-- -->
<!-- -->
<?php function render_select( $args ) { ?>
<select data-grp-tgt="<?php echo out( $args['target'] ); ?>"
        type="dropdown"
        name="<?php echo out( $args['name'] ); ?>"
        id="<?php echo out( $args['id'] ); ?>">

    <!-- default option -->
    <option id="" value="">--<?php echo out( xl( 'Select' ) ); ?>--</option>

    <!-- iterate over other options -->
    <?php foreach( $args['options'] as $option ) { ?>
    <option id="<?php echo out( $option['id'] ); ?>" 
            value="<?php echo out( $option['id'] ); ?>"
            <?php echo $args['value'] == $option['id'] ? "SELECTED" : "" ?>>
        <?php echo out( xl( $option['label'] ) ); ?>
    </option>
    <?php } ?>

</select>
<?php } ?>

<!-- -->
<!-- -->
<!-- -->
<?php function textfield_row( $args ) { ?>
<p class="row">
    <span class="left_col colhead req" data-field="<?php echo out( $args['name'] ); ?>"><?php echo out( $args['title'] ); ?></span>
    <span class="end_col">
        <input id="<?php echo $args['id'] ? out( $args['id'] ) : ""?>"
               data-grp-tgt="<?php echo out( $args['target'] ); ?>" class="field <?php echo out( $args['class'] ); ?>"
               type="text"
               name="<?php echo out( $args['name'] ); ?>"
               value="<?php echo out( $args['value'] );?>" />
    </span>
</p>
<?php } ?>

<!--

Compound Helpers

-->

<!-- -->
<!-- -->
<!-- -->
<?php function common_fields( $args ) { ?>
    <?php $criteria = $args['criteria'];  ?>
    <p class="row">
        <span class="left_col colhead req" data-field="fld_optional"><?php echo out( xl( 'Optional' ) ); ?></span>
        <span class="end_col">
            <input id="fld_optional" type="radio" name="fld_optional" class="field" value="yes"
                   <?php echo $criteria->optional ? "CHECKED" : ""?>> <?php echo out( xl( 'Yes' ) ); ?>
            <input id="fld_optional" type="radio" name="fld_optional" class="field" value="no"
                   <?php echo !$criteria->optional ? "CHECKED" : ""?>> <?php echo out( xl( 'No' ) ); ?>
        </span>
    </p>

    <p class="row">
        <span class="left_col colhead req" data-field="fld_inclusion"><?php echo out( xl( 'Inclusion' ) ); ?></span>
        <span class="end_col">
            <input id="fld_inclusion" type="radio" name="fld_inclusion" class="field" value="yes"
                   <?php echo $criteria->inclusion ? "CHECKED" : ""?>> <?php echo out( xl( 'Yes' ) ); ?>
            <input id="fld_inclusion" type="radio" name="fld_inclusion" class="field" value="no"
                   <?php echo !$criteria->inclusion ? "CHECKED" : ""?>> <?php echo out( xl( 'No' ) ); ?>
        </span>
    </p>

    <?php if ( $criteria->interval && $criteria->intervalType )  { ?>
    <p class="row">
        <span class="left_col colhead req" data-field="fld_target_interval"><?php echo out( xl( 'Interval' ) ); ?></span>
        <span class="end_col">
            <input data-grp-tgt="flt_target_interval" class="field short"
                   type="text"
                   name="fld_target_interval"
                   value="<?php echo out( xl ( $criteria->interval ) ); ?>" />

            <?php echo timeunit_select( array( "context"=>"rule_target_intervals", "target"=>"fld_target_interval_", "name" => "fld_target_interval_type", "value" => $criteria->intervalType ) ); ?>
        </span>
    </p>
    <?php } ?>
<?php } ?>

<!--                  -->
<!-- render time unit -->
<!--                  -->
<?php function timeunit_select( $args ) { 
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
        array( "data-grp-tgt" => $args['target'] ) );
} ?>

