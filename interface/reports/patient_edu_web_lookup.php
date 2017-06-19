<?php
/**
 * Open websearch for patient education materials
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Tony McCormick <tony@mi-squared.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * Copyright (C) 2011 Tony McCormick <tony@mi-squared.com>
 * Copyright (C) 2011 Brady Miller   <brady.g.miller@gmail.com>
 * Copyright (C) 2017 Roberto Vasquez <robertogagliotta@gmail.com>
 * @license  https://github.com/openemr/openemr/blob/master/LICENSE CNU General Public License 3
 *
 */

//Include required scripts/libraries
require_once("../globals.php");

use OpenEMR\Core\Header;

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
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Web Search'); ?> - <?php echo xlt('Patient Education Materials'); ?></title>
<script type="text/javascript">
  function searchResultsPopup(search_term,link) {
    link_formatted = link.replace("[%]",encodeURIComponent(search_term));
    top.restoreSession();
    window.open(link_formatted);
  }
</script>

</head>

<body class="body_top" onload="javascripts:document.forms[0].form_import_data.focus()">
<div class="container">
  <div class="row">
     <div class="col-xs-12">
       <div class="page-header">
         <h2><?php echo  xlt('Web Search'); ?> - <?php echo xlt('Patient Education Materials'); ?></h2>
       </div>
     </div>
  </div>
</div>
<form method='post'  action='patient_edu_web_lookup.php' id='theform' onsubmit='return top.restoreSession()'>

<div class="row">
  <div class="col-xs-12">
     <div class="form-group">
           <label class='control-label'>
			   <?php echo xlt('Search in'); 
                             echo '&nbsp;&nbsp;';
				echo "<select name='form_lookup_at'  class='form-control'\n>";
				foreach ($websites as $key => $value) {
				  echo "    <option value='" . attr($key) . "'";
				  if ($key == $form_lookup_at) echo ' selected';
				  echo ">" .  text($key) . "</option>\n";
				}
				echo "</select>"; ?>
           </label>
     </div>
   </div>
</div>
  <div class="row">
          <div class="form-group"> 
          <div class="col-xs-6">
                <input name='form_diagnosis' class='form-control' aria-describedby='searchHelpBox' rows='1' value='<?php echo xla($form_diagnosis); ?>'
				title='<?php echo  xla('Search Text'); ?>'></input>
   <div class="form-group">
     <button type='submit' class='btn btn-default btn-save' onclick='top.restoreSession(); $("#theform").submit()' > <?php echo xlt("Submit"); ?> </button>
   </div>
                
          </div>
          </div>

    </div>

<span id="searchHelpBox" class="help-block">
<?php
  echo xlt('Please input search criteria above, and click Submit to view results. (Results will be displayed in a pop up window)');
?>
</span>
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
