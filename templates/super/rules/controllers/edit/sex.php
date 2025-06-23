<?php

/**
 * interface/super/rules/controllers/edit/view/sex.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\ClinicalDecisionRules\Interface\RuleTemplateExtension;

?>
<p class="form-row">
    <span class="left_col colhead req" data-fld="fld_sex"><?php echo xlt('Sex');?></span>
    <span class="end_col">
    <?php echo RuleTemplateExtension::render_select(array( "target"   =>  "fld_sex",
                                     "name"     =>  "fld_sex",
                                     "value"    =>  $criteria->value,
                                     "options"  =>  $criteria->getOptions() )); ?>
    </span>
</p>

<br/>

<!-- optional/required and inclusion/exclusion fields -->
<?php echo RuleTemplateExtension::common_fields(array( "criteria" => $criteria)); ?>
