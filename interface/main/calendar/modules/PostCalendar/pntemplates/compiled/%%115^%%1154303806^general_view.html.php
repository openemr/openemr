<?php /* Smarty version 2.6.2, created on 2015-07-31 10:01:59
         compiled from E:/web/Apache24/htdocs/openemr_community/templates/documents/general_view.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', 'E:/web/Apache24/htdocs/openemr_community/templates/documents/general_view.html', 37, false),array('function', 'user_info', 'E:/web/Apache24/htdocs/openemr_community/templates/documents/general_view.html', 259, false),array('modifier', 'escape', 'E:/web/Apache24/htdocs/openemr_community/templates/documents/general_view.html', 192, false),)), $this); ?>
<head>
<style type="text/css">@import url(library/dynarch_calendar.css);</style>
<script type="text/javascript" src="library/dialog.js"></script>
<script type="text/javascript" src="library/textformat.js"></script>
<script type="text/javascript" src="library/dynarch_calendar.js"></script>
<?php  include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php");  ?>
<script type="text/javascript" src="library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="library/js/jquery-1.9.1.min.js"></script>
<script language="JavaScript">
 var mypcc = '<?php  echo $GLOBALS['phone_country_code']  ?>';

 // Process click on Delete link.
 function deleteme(docid) <?php echo '{'; ?>

  dlgopen('interface/patient_file/deleter.php?document=' + docid, '_blank', 500, 450);
  return false;
 <?php echo '}'; ?>


 // Called by the deleter.php window on a successful delete.
 function imdeleted() <?php echo '{'; ?>

  top.restoreSession();
  window.location.href='<?php echo $this->_tpl_vars['REFRESH_ACTION']; ?>
';
 <?php echo '}'; ?>


 // Called to show patient notes related to this document in the "other" frame.
 function showpnotes(docid) <?php echo '{'; ?>

<?php  if ($GLOBALS['concurrent_layout']) {  ?>
  var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
  parent.left_nav.forceDual();
  parent.left_nav.setRadio(othername, 'pno');
  parent.left_nav.loadFrame('pno1', othername, 'patient_file/summary/pnotes.php?docid=' + docid);
<?php  }  ?>
  return false;
 <?php echo '}'; ?>


 function submitNonEmpty( e ) <?php echo '{'; ?>

	if ( e.elements['passphrase'].value.length == 0 ) <?php echo '{'; ?>

		alert( "<?php echo smarty_function_xl(array('t' => 'You must enter a pass phrase to encrypt the document'), $this);?>
" );
	<?php echo '}'; ?>
 else <?php echo '{'; ?>

		e.submit();
	<?php echo '}'; ?>

 <?php echo '}'; ?>


// For tagging it encounter
function tagUpdate() <?php echo '{'; ?>

	var f = document.forms['document_tag'];
	if (f.encounter_check.checked) <?php echo '{'; ?>

		if(f.visit_category_id.value==0) <?php echo '{'; ?>

			alert(" <?php echo smarty_function_xl(array('t' => 'Please select visit category'), $this);?>
" );
			return false;
		<?php echo '}'; ?>

	<?php echo '}'; ?>
 else if (f.encounter_id.value == 0 ) <?php echo '{'; ?>

		alert(" <?php echo smarty_function_xl(array('t' => 'Please select encounter'), $this);?>
");
		return false;	
	<?php echo '}'; ?>

	//top.restoreSession();
	document.forms['document_tag'].submit();
<?php echo '}'; ?>


// For new or existing encounter
function set_checkbox() <?php echo '{'; ?>

	var f = document.forms['document_tag'];
	if (f.encounter_check.checked) <?php echo '{'; ?>

		f.encounter_id.disabled = true;
		f.visit_category_id.disabled = false;
	<?php echo '}'; ?>
 else <?php echo '{'; ?>

		f.encounter_id.disabled = false;
		f.visit_category_id.disabled = true;
		f.visit_category_id.value = 0;
	<?php echo '}'; ?>

<?php echo '}'; ?>


 // Process click on Import link.
 function import_ccr(docid) <?php echo '{
  top.restoreSession();
  $.ajax({
    url: "library/ajax/ccr_import_ajax.php",
    type: "POST",
    dataType: "html",
    data:
    {
      ccr_ajax : "yes",
      document_id : docid,
    },
    success: function(data){
      alert(data);
      top.restoreSession();
      document.location.reload();
    },
    error:function(){
      alert("failure");
    }
  });
 }'; ?>



</script>

</head>

<table valign="top" width="100%">
    <tr>
        <td>
            <a class="css_button" href="<?php echo $this->_tpl_vars['web_path']; ?>
" onclick="top.restoreSession()"><span><?php echo smarty_function_xl(array('t' => 'Download'), $this);?>
</span></a>
            <a class="css_button" href='' onclick='return showpnotes(<?php echo $this->_tpl_vars['file']->get_id(); ?>
)'><span><?php echo smarty_function_xl(array('t' => 'Show Notes'), $this);?>
</span></a>
            <?php echo $this->_tpl_vars['delete_string']; ?>

            <?php if ($this->_tpl_vars['file']->get_ccr_type ( $this->_tpl_vars['file']->get_id() ) == 'CCR' && ( $this->_tpl_vars['file']->get_mimetype ( $this->_tpl_vars['file']->get_id() ) == "application/xml" || $this->_tpl_vars['file']->get_mimetype ( $this->_tpl_vars['file']->get_id() ) == "text/xml" ) && $this->_tpl_vars['file']->get_imported ( $this->_tpl_vars['file']->get_id() ) == 0): ?>
            <a class="css_button" href='javascript:' onclick='return import_ccr(<?php echo $this->_tpl_vars['file']->get_id(); ?>
)'><span><?php echo smarty_function_xl(array('t' => 'Import'), $this);?>
</span></a>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
		<td valign="top">
			<?php if (! $this->_tpl_vars['hide_encryption']): ?>
			<div class="text">
                <form method="post" name="document_encrypt" action="<?php echo $this->_tpl_vars['web_path']; ?>
" onsubmit="return top.restoreSession()">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => 'Encryption'), $this);?>
</b>&nbsp; 
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="submitNonEmpty( document.forms['document_encrypt'] );">(<span><?php echo smarty_function_xl(array('t' => 'download encrypted file'), $this);?>
)</span></a>
                    </div> 
                </div>
                <div>
                    <?php echo smarty_function_xl(array('t' => 'Pass Phrase'), $this);?>
:
                    <input title="<?php echo smarty_function_xl(array('t' => 'Supports TripleDES encryption/decryption only.'), $this);?>
 <?php echo smarty_function_xl(array('t' => 'Leaving the pass phrase blank will not encrypt the document'), $this);?>
" type='text' size='20' name='passphrase' id='passphrase' value=''/>
                    <input type="hidden" name="encrypted" value="true"></input>
              	</div>
                </form>
            </div>
            <br/>
            <?php endif; ?>
			<div class="text">
                <form method="post" name="document_validate" action="<?php echo $this->_tpl_vars['VALIDATE_ACTION']; ?>
" onsubmit="return top.restoreSession()">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => 'Sha-1 Hash'), $this);?>
:</b>&nbsp;
                        <i><?php echo $this->_tpl_vars['file']->get_hash(); ?>
</i>&nbsp;
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="document.forms['document_validate'].submit();">(<span><?php echo smarty_function_xl(array('t' => 'validate'), $this);?>
)</span></a>
                    </div>
                </div>
                </form>
            </div>
            <br/>
            <div class="text">
                <form method="post" name="document_update" action="<?php echo $this->_tpl_vars['UPDATE_ACTION']; ?>
" onsubmit="return top.restoreSession()">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => 'Update'), $this);?>
</b>&nbsp;
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="document.forms['document_update'].submit();">(<span><?php echo smarty_function_xl(array('t' => 'submit'), $this);?>
)</span></a>
                    </div>
                </div>
                <div>
                    <?php echo smarty_function_xl(array('t' => 'Rename'), $this);?>
:
                    <input type='text' size='20' name='docname' id='docname' value='<?php echo $this->_tpl_vars['file']->get_url_web(); ?>
'/>
              	</div>
                <div>
                    <?php echo smarty_function_xl(array('t' => 'Date'), $this);?>
:
                    <input type='text' size='10' name='docdate' id='docdate'
                     value='<?php echo $this->_tpl_vars['DOCDATE']; ?>
' title='<?php echo smarty_function_xl(array('t' => 'yyyy-mm-dd document date'), $this);?>
'
                     onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
                    <img src='interface/pic/show_calendar.gif' id='img_docdate' align='absbottom'
                     width='24' height='22' border='0' alt='[?]' style='cursor:pointer'
                     title='<?php echo smarty_function_xl(array('t' => 'Click here to choose a date'), $this);?>
' />
                    <select name="issue_id"><?php echo $this->_tpl_vars['ISSUES_LIST']; ?>
</select>
                </div>
                </form>
            </div>

            <br/>

            <div class="text">
                <form method="post" name="document_move" action="<?php echo $this->_tpl_vars['MOVE_ACTION']; ?>
" onsubmit="return top.restoreSession()">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => 'Move'), $this);?>
</b>&nbsp;
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="document.forms['document_move'].submit();">(<span><?php echo smarty_function_xl(array('t' => 'submit'), $this);?>
)</span></a>
                    </div>
                </div>

                <div>
                        <select name="new_category_id"><?php echo $this->_tpl_vars['tree_html_listbox']; ?>
</select>&nbsp;
                        <?php echo smarty_function_xl(array('t' => 'Move to Patient'), $this);?>
 # <input type="text" name="new_patient_id" size="4" />
                        <a href="javascript:<?php echo '{}'; ?>
"
                         onclick="top.restoreSession();var URL='controller.php?patient_finder&find&form_id=<?php echo ((is_array($_tmp="document_move['new_patient_id']")) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
&form_name=<?php echo ((is_array($_tmp="document_move['new_patient_name']")) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
'; window.open(URL, 'document_move', 'toolbar=0,scrollbars=1,location=0,statusbar=1,menubar=0,resizable=1,width=450,height=400,left=425,top=250');">
                        <img src="images/stock_search-16.png" border="0" /></a>
                        <input type="hidden" name="new_patient_name" value="" />
                </div>
                </form>
            </div>

			<br/>
			
			<div class="text">
			   <form method="post" name="document_tag" id="document_tag" action="<?php echo $this->_tpl_vars['TAG_ACTION']; ?>
" onsubmit="return top.restoreSession()">

				<div >
				   <div style="float:left">
					   <b><?php echo smarty_function_xl(array('t' => 'Tag to Encounter'), $this);?>
</b>&nbsp;
				   </div>

				   <div style="float:none">
					   <a href="javascript:;" onclick="tagUpdate();">(<span><?php echo smarty_function_xl(array('t' => 'submit'), $this);?>
)</span></a>
				   </div>
			   </div>

				 <div>
					<select id="encounter_id"  name="encounter_id"  ><?php echo $this->_tpl_vars['ENC_LIST']; ?>
</select>&nbsp;
					<input type="checkbox" name="encounter_check" id="encounter_check"  onclick='set_checkbox(this)'/> <label for="encounter_check"><b><?php echo smarty_function_xl(array('t' => 'Create Encounter'), $this);?>
</b></label>&nbsp;&nbsp;
					   <?php echo smarty_function_xl(array('t' => 'Visit Category'), $this);?>
 : &nbsp;<select id="visit_category_id"  name="visit_category_id"  disabled><?php echo $this->_tpl_vars['VISIT_CATEGORY_LIST']; ?>
</select>&nbsp; 

			   </div>
			   </form>
		   </div>

            <br/>

            <form name="notes" method="post" action="<?php echo $this->_tpl_vars['NOTE_ACTION']; ?>
" onsubmit="return top.restoreSession()">
            <div class="text">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => 'Notes'), $this);?>
</b>&nbsp;
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="document.notes.identifier.value='no';document.forms['notes'].submit();">(<span><?php echo smarty_function_xl(array('t' => 'add'), $this);?>
</span>)</a>
                    	&nbsp;&nbsp;&nbsp;<b><?php echo smarty_function_xl(array('t' => 'Email'), $this);?>
</b>&nbsp;
                    	<input type="text" size="25" name="provide_email" id="provide_email" />
                    	<input type="hidden" name="identifier" id="identifier" />
                        <a href="javascript:;" onclick="javascript:document.notes.identifier.value='yes';document.forms['notes'].submit();">
                        	(<span><?php echo smarty_function_xl(array('t' => 'Send'), $this);?>
</span>)
                        </a>
                    </div>
                    <div>
                        
                    </div>
                    <div style="float:none">
                    	
                    </div>
                <div>
                    <textarea cols="53" rows="8" wrap="virtual" name="note" style="width:100%"></textarea><br>
                    <input type="hidden" name="process" value="<?php echo $this->_tpl_vars['PROCESS']; ?>
" />
                    <input type="hidden" name="foreign_id" value="<?php echo $this->_tpl_vars['file']->get_id(); ?>
" />

                    <?php if ($this->_tpl_vars['notes']): ?>
                    <div style="margin-top:7px">
                        <?php if (isset($this->_foreach['note_loop'])) unset($this->_foreach['note_loop']);
$this->_foreach['note_loop']['name'] = 'note_loop';
$this->_foreach['note_loop']['total'] = count($_from = (array)$this->_tpl_vars['notes']);
$this->_foreach['note_loop']['show'] = $this->_foreach['note_loop']['total'] > 0;
if ($this->_foreach['note_loop']['show']):
$this->_foreach['note_loop']['iteration'] = 0;
    foreach ($_from as $this->_tpl_vars['note']):
        $this->_foreach['note_loop']['iteration']++;
        $this->_foreach['note_loop']['first'] = ($this->_foreach['note_loop']['iteration'] == 1);
        $this->_foreach['note_loop']['last']  = ($this->_foreach['note_loop']['iteration'] == $this->_foreach['note_loop']['total']);
?>
                        <div>
                        <?php echo smarty_function_xl(array('t' => 'Note'), $this);?>
 #<?php echo $this->_tpl_vars['note']->get_id(); ?>

                        <?php echo smarty_function_xl(array('t' => 'Date:'), $this);?>
 <?php echo $this->_tpl_vars['note']->get_date(); ?>

                        <?php echo $this->_tpl_vars['note']->get_note(); ?>

                        <?php if ($this->_tpl_vars['note']->get_owner()): ?>
                            &nbsp;-<?php echo smarty_function_user_info(array('id' => $this->_tpl_vars['note']->get_owner()), $this);?>

                        <?php endif; ?>
                        </div>
                        <?php endforeach; unset($_from); endif; ?>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            </form>

		</td>
	</tr>
	<tr>
		<td>
            <div class="text"><b><?php echo smarty_function_xl(array('t' => 'Content'), $this);?>
</b></div>
            <?php if ($this->_tpl_vars['file']->get_mimetype() == "image/tiff"): ?>
			<embed frameborder="0" type="<?php echo $this->_tpl_vars['file']->get_mimetype(); ?>
" src="<?php echo $this->_tpl_vars['web_path']; ?>
as_file=false"></embed>
			<?php elseif ($this->_tpl_vars['file']->get_mimetype() == "image/png" || $this->_tpl_vars['file']->get_mimetype() == "image/jpg" || $this->_tpl_vars['file']->get_mimetype() == "image/jpeg" || $this->_tpl_vars['file']->get_mimetype() == "image/gif" || $this->_tpl_vars['file']->get_mimetype() == "application/pdf"): ?>
			<iframe frameborder="0" type="<?php echo $this->_tpl_vars['file']->get_mimetype(); ?>
" src="<?php echo $this->_tpl_vars['web_path']; ?>
as_file=false"></iframe>
			<?php elseif ($this->_tpl_vars['file']->get_ccr_type ( $this->_tpl_vars['file']->get_id() ) != 'CCR' && $this->_tpl_vars['file']->get_ccr_type ( $this->_tpl_vars['file']->get_id() ) != 'CCD'): ?>
			<iframe frameborder="0" type="<?php echo $this->_tpl_vars['file']->get_mimetype(); ?>
" src="<?php echo $this->_tpl_vars['web_path']; ?>
as_file=true"></iframe>
			<?php endif; ?>
		</td>
	</tr>
</table>
<script language='JavaScript'>
 Calendar.setup(<?php echo '{'; ?>
inputField:"docdate", ifFormat:"%Y-%m-%d", button:"img_docdate"<?php echo '}'; ?>
);
</script>