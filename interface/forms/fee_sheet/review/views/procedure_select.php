<?php

/**
 * knockoutjs template for rendering the procedure selector when reviewing
 * old fee sheets
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<script type="text/html" id="procedure-select">
    <select class="form-control" data-bind="options: procedure_choices, optionsText: function(item){ return (item.code + ' ' + item.description);}, value:procedure_choice, event: {change: change_procedure}"></select>
</script>
