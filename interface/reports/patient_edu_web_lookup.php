<?php
/**
 * Open websearch for patient education materials
 *
 * Copyright (C) 2011 Tony McCormick <tony@mi-squared.com>
 * Copyright (C) 2011 Brady Miller   <brady@sparmy.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Tony McCormick <tony@mi-squared.com>
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */


//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

//Include required scripts/libraries
require_once("../globals.php");


// Reference website links
$websites = array(
  'Medline'   => 'http://vsearch.nlm.nih.gov/vivisimo/cgi-bin/query-meta?v%3Aproject=medlineplus&query=[%]&x=12&y=15',
  'eMedicine' => 'http://search.medscape.com/reference-search?newSearchHeader=1&queryText=[%]',
  'WebMD'     => 'http://www.webmd.com/search/search_results/default.aspx?query=[%]&sourceType=undefined' 
);

// Collect variables
$form_lookup_at = (isset($_POST['form_lookup_at'])) ? $_POST['form_lookup_at'] : '';
$form_diagnosis = (isset($_POST['form_diagnosis'])) ? $_POST['form_diagnosis'] : '';
?>

<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<script type="text/javascript">
  function searchResultsPopup(search_term,link) {
    link_formatted = link.replace("[%]",encodeURIComponent(search_term));
    top.restoreSession();
    window.open(link_formatted);
  }
</script>

<title><?php echo htmlspecialchars( xl('Find Patient Education Materials'), ENT_NOQUOTES); ?></title>
</head>

<body class="body_top">

<span class='title'><?php echo htmlspecialchars( xl('Web Search'), ENT_NOQUOTES); ?> - <?php echo htmlspecialchars( xl('Patient Education Materials'), ENT_NOQUOTES); ?></span>

<form method='post' action='patient_edu_web_lookup.php' id='theform' onsubmit='return top.restoreSession()'>

<div id="report_parameters">

<table>
 <tr>
  <td>
	<div style='float:left'>
	<table class='text'>
		<tr>
			<td>
			   <?php echo htmlspecialchars( xl('Search in'), ENT_NOQUOTES);
                echo '&nbsp;&nbsp;';
				echo "<select name='form_lookup_at'>\n";
				foreach ($websites as $key => $value) {
				  echo "    <option value='" . htmlspecialchars($key, ENT_QUOTES) . "'";
				  if ($key == $form_lookup_at) echo ' selected';
				  echo ">" . htmlspecialchars( xl($key), ENT_NOQUOTES) . "</option>\n";
				}
				echo "</select>"; ?>
			</td>
        </tr>
        <tr>
            <td>
			   <input type='text' name='form_diagnosis' size='60' value='<?php echo htmlspecialchars($form_diagnosis, ENT_QUOTES); ?>'
				title='<?php echo htmlspecialchars( xl('Search Text'), ENT_QUOTES); ?>'>
			</td>
		</tr>
	</table>

	</div>

  </td>
  <td align='left' valign='middle' height="100%">
	<table style='border-left:1px solid; width:100%; height:100%' >
		<tr>
			<td>
				<div style='margin-left:15px'>
					<a href='#' class='css_button' onclick='top.restoreSession(); $("#theform").submit();'>
					<span>
						<?php echo htmlspecialchars( xl('Submit'), ENT_NOQUOTES); ?>
					</span>
					</a>

				</div>
			</td>
		</tr>
	</table>
  </td>
 </tr>
</table>

</div> <!-- end of parameters -->

<div class='text'>
<?php
  echo htmlspecialchars( xl('Please input search criteria above, and click Submit to view results. (Results will be displayed in a pop up window)'), ENT_NOQUOTES);
?>
</div>
<div class='text'>
<?php if (!empty($form_diagnosis) && !empty($form_lookup_at)) { ?>
    <script type="text/javascript">
      searchResultsPopup('<?php echo addslashes($form_diagnosis); ?>','<?php echo addslashes($websites[$form_lookup_at]) ?>');
    </script>
<?php } ?>
</div>
</form>
</body>
</html>
