<?php
//// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.



require_once("../interface/globals.php");
?>
<html>
<head>
  <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">
 body {
  font-size:8pt;
  font-weight:normal;
  padding: 5px 3px 5px 3px;
  background: #94D6E7;
 }
</style>
<script language="javascript">
    function doSelectorButton() {
        var selector = document.getElementById('selectorButton');
        var value;
        if ( selector.value == <?php echo xlj('Select All'); ?> ) {
            selector.value = <?php echo xlj('Unselect All'); ?>;
            value = true;
        } else {
            selector.value = <?php echo xlj('Select All'); ?>;
            value = false;
        }
        var checkBoxes = document.getElementsByName( "searchFields" );
        setAll( checkBoxes, value );
    }

    function setAll(field, value) {
        for (i = 0; i < field.length; i++) {
            field[i].checked = value ;
        }
    }

    function doSubmit() {
        // buildup fieldstring
        var checkBoxes = document.getElementsByName( "searchFields" );
        var fieldString = '';
    var ssc = document.forms[0].search_service_code.value;
        for (i = 0; i < checkBoxes.length; i++) {
            if ( checkBoxes[i].checked ) {
                if ( fieldString != '' ) {
                    fieldString += "~";
                }
                fieldString += checkBoxes[i].value;
            }
        }
        if ( opener != null ) {
            if (fieldString == undefined || (fieldString == '' && ssc.length == '')) {
                alert(<?php echo xlj('You must select some fields to continue.'); ?>);
                return false;
            }
            opener.processFilter(fieldString, ssc);
        }
    }

</script>
</head>

<body>
<form>

    <table>
      <tr>
        <td>
          <b><?php echo xlt('Select Fields'); ?>:</b>
        </td>
        <td>
        <input type="button" value="<?php echo xla('Submit'); ?>" id="submit" onclick="javascript:doSubmit();"></input>
        </td>
        <td>
        <input type="button" value="<?php echo xla('Select All'); ?>" id="selectorButton" onclick="javascript:doSelectorButton();"></input>
        </td>
      </tr>
    </table>

    <?php
    function echoFilterItem($iter, $fieldId, $fieldTitle)
    {
        if ($iter % 3 == 0) {
            if ($iter > 0) {
                echo "</tr>\n";
            }

            echo "<tr>\n";
        }

            echo "<td>";
            echo "<input type='checkbox' value='" . attr($fieldId) . "' name='searchFields'/> <b>" . text($fieldTitle) . "</b>";
            echo "</td>\n";
    }

        $layoutCols = sqlStatement("SELECT field_id, title, description, group_id "
      . "FROM layout_options "
      . "WHERE form_id='DEM' "
      . "AND field_id not like 'em\_%' AND uor != 0 "
      . "ORDER BY group_id,seq");

        echo "<table>";

        for ($iter=0; $row=sqlFetchArray($layoutCols); $iter++) {
            $label = $row['title'] ? $row['title'] : $row['description'];
            if (!$label) {
                $label = $row['field_id'];
            }

            echoFilterItem(
                $iter,
                $row['field_id'],
                xl_layout_label($label)
            );
        }

        echoFilterItem($iter++, 'pid', xl('Internal Identifier (pid)'));

        // Finish the row gracefully.
        while ($iter++ % 3) {
            echo "<td>&nbsp;</td>\n";
        }

        echo "</tr>\n";

        // Write a final line to solicit an optional service code.
        echo "<tr>\n";
        echo "<td colspan='3'>";
        echo "<input type='text' value='' name='search_service_code' size='8' /> " .
            "<b>" . xlt('Service Code') . "</b> (" .
            xlt('if entered, select only those who have had this service') . ")";
        echo "</td>\n";
        echo "</tr>\n";

        echo "</table>";
        ?>

</form>
</body>
</html>
