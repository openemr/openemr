<?php
/**
 * Generated DocBlock
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  shahar zi <shaharzi@matrix.co.il>
 * @author  Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 shahar zi <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
?>
<div id="component-border">
    <div class="row">
        <div class="col-md-12">
            <h4><?php echo xlt('Past meetings')?></h4>
            <button onclick="newGroup()"><?php echo xlt('Add encounter')?></button>
        </div>
    </div>
</div>
<script>
    function newGroup(){
        top.restoreSession();
        top.frames['RBot'].location = '<?php echo $GLOBALS['web_root'] . "/interface/" ?>' + 'forms/newGroupEncounter/new.php?autoloaded=1&calenc=';
        //top.window.parent.left_nav.loadFrame2('nen1','RBot','forms/newGroupEncounter/new.php?autoloaded=1&calenc=')

    }
</script>
