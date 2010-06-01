<?php
//// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../interface/globals.php");
require_once("../library/sql.inc");

?>

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
		if ( selector.value == "<?php xl('Select All','e'); ?>" ) {
			selector.value = "<?php xl('Unselect All','e'); ?>";
			value = true;
		} else {
			selector.value = "<?php xl('Select All','e'); ?>";
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
		for (i = 0; i < checkBoxes.length; i++) {
			if ( checkBoxes[i].checked ) {
				if ( fieldString != '' ) {
					fieldString += "~";
				}
				fieldString += checkBoxes[i].value;
			}
		}
	    if ( opener != null ) {
			if ( fieldString == '' || fieldString == undefined ) {
				alert("<?php xl('You must select some fields to continue.','e'); ?>");
				return false;
			}
			opener.processFilter( fieldString );
        }
	}

  </script>
</head>

<body>
	<table>
	  <tr>
		<td>
		  <b><?php xl('Select Fields', 'e'); ?>:</b>
		</td>
	    <td>
		<input type="button" value="<?php xl('Submit','e'); ?>" id="submit" onclick="javascript:doSubmit();"></input>
	    </td>
		<td>
		<input type="button" value="<?php xl('Select All','e'); ?>" id="selectorButton" onclick="javascript:doSelectorButton();"></input>
		</td>
	  </tr>
	</table>

	<?php
    function echoFilterItem($iter, $fieldId, $fieldTitle) {
			if ( $iter == 0 || ($iter % 3 == 0) ) {
				if ( $iter > 0 ) {
					echo "</tr>";
				}
				echo "<tr>";
			}
			echo "<td>";
			echo "<input type='checkbox' value='${fieldId}' name='searchFields'/> <b>$fieldTitle</b>";
			echo "</td>";
    }

		$layoutCols = sqlStatement( "SELECT field_id, title, description, group_name "
      . "FROM layout_options "
      . "WHERE form_id='DEM' "
      . "AND group_name not like ('%Employer%' ) AND uor !=0 "
      . "ORDER BY group_name,seq"
		);

		echo "<table>";

		for($iter=0; $row=sqlFetchArray($layoutCols); $iter++) {
		    $label = $row['title'] ? $row['title'] : $row['description'];
		    if ( !$label ) {
		        $label = $row['field_id'];
		    }
            echoFilterItem(
                $iter,
                $row['field_id'],
                xl_layout_label($label)
            );
		}
    echoFilterItem($iter, 'pid', xl('Internal Identifier (pid)'));

		echo "</table>";
	?>

</body>
