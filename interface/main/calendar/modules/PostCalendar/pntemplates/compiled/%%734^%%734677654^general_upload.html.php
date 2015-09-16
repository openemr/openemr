<?php /* Smarty version 2.6.2, created on 2015-07-28 21:15:43
         compiled from E:/web/Apache/htdocs/openemr_community/templates/documents/general_upload.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', 'E:/web/Apache/htdocs/openemr_community/templates/documents/general_upload.html', 6, false),)), $this); ?>
<form method=post enctype="multipart/form-data" action="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
" onsubmit="return top.restoreSession()">
<input type="hidden" name="MAX_FILE_SIZE" value="64000000" />

<?php if (( ! ( $this->_tpl_vars['patient_id'] > 0 ) )): ?>
  <div class="text" style="color:red;">
    <?php echo smarty_function_xl(array('t' => "IMPORTANT: This upload tool is only for uploading documents on patients that are not yet entered into the system. To upload files for patients whom already have been entered into the system, please use the upload tool linked within the Patient Summary screen."), $this);?>

    <br/>
    <br/>
  </div>
<?php endif; ?>

<div class="text">
    <?php echo smarty_function_xl(array('t' => "NOTE: Uploading files with duplicate names will cause the files to be automatically renamed (for example, file.jpg will become file.1.jpg). Filenames are considered unique per patient, not per category."), $this);?>

    <br/>
    <br/>
</div>
<div class="text bold">
    <?php echo smarty_function_xl(array('t' => 'Upload Document'), $this);?>
 <?php if ($this->_tpl_vars['category_name']): ?> <?php echo smarty_function_xl(array('t' => 'to category'), $this);?>
 '<?php echo $this->_tpl_vars['category_name']; ?>
'<?php endif; ?>
</div>
<div class="text">
    <p><span><?php echo smarty_function_xl(array('t' => 'Source File Path'), $this);?>
:</span> <input type="file" name="file[]" id="source-name" multiple="true"/>&nbsp;(<font size="1"><?php echo smarty_function_xl(array('t' => "Multiple files can be uploaded at one time by selecting them using CTRL+Click or SHIFT+Click."), $this);?>
</font>)</p>
    <p><span title="<?php echo smarty_function_xl(array('t' => 'Leave Blank To Keep Original Filename'), $this);?>
"><?php echo smarty_function_xl(array('t' => 'Optional Destination Name'), $this);?>
:</span> <input type="text" name="destination" title="<?php echo smarty_function_xl(array('t' => 'Leave Blank To Keep Original Filename'), $this);?>
" id="destination-name" /></p>
    <?php if (! $this->_tpl_vars['hide_encryption']): ?>
	</br>
	<p><span title="<?php echo smarty_function_xl(array('t' => 'Check the box if this is an encrypted file'), $this);?>
"><?php echo smarty_function_xl(array('t' => "Is The File Encrypted?"), $this);?>
:</span> <input type="checkbox" name="encrypted" title="<?php echo smarty_function_xl(array('t' => 'Check the box if this is an encrypted file'), $this);?>
" id="encrypted" /></p>
	<p><span title="<?php echo smarty_function_xl(array('t' => 'Pass phrase to decrypt document'), $this);?>
"><?php echo smarty_function_xl(array('t' => 'Pass Phrase'), $this);?>
:</span> <input type="text" name="passphrase" title="<?php echo smarty_function_xl(array('t' => 'Pass phrase to decrypt document'), $this);?>
" id="passphrase" /></p>
	<p><i><?php echo smarty_function_xl(array('t' => 'Supports TripleDES encryption/decryption only.'), $this);?>
</i></p>
    <?php endif; ?>
    <p><input type="submit" value="<?php echo smarty_function_xl(array('t' => 'Upload'), $this);?>
" /></p>
</div>

<input type="hidden" name="patient_id" value="<?php echo $this->_tpl_vars['patient_id']; ?>
" />
<input type="hidden" name="category_id" value="<?php echo $this->_tpl_vars['category_id']; ?>
" />
<input type="hidden" name="process" value="<?php echo $this->_tpl_vars['PROCESS']; ?>
" />
</form>

<!-- Section for document template download -->
<form method='post' action='interface/patient_file/download_template.php' onsubmit='return top.restoreSession()'>
<input type='hidden' name='patient_id' value='<?php echo $this->_tpl_vars['patient_id']; ?>
' />
<p class='text bold'>
 <?php echo smarty_function_xl(array('t' => 'Download document template for this patient and visit'), $this);?>

</p>
<p class='text'>
 <select name='form_filename'><?php echo $this->_tpl_vars['TEMPLATES_LIST']; ?>
</select> &nbsp;
 <input type='submit' value='<?php echo smarty_function_xl(array('t' => 'Fetch'), $this);?>
' />
</p>
</form>
<!-- End document template download section -->

<?php if (! empty ( $this->_tpl_vars['file'] )): ?>
	<div class="text bold">
		<br/>
		<?php echo smarty_function_xl(array('t' => 'Upload Report'), $this);?>

	</div>
	<?php if (count($_from = (array)$this->_tpl_vars['file'])):
    foreach ($_from as $this->_tpl_vars['file']):
?>
		<div class="text">
			<?php if ($this->_tpl_vars['error']): ?><i><?php echo $this->_tpl_vars['error']; ?>
</i><br/><?php endif; ?>
			<?php echo smarty_function_xl(array('t' => 'ID'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_id(); ?>
<br>
			<?php echo smarty_function_xl(array('t' => 'Patient'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_foreign_id(); ?>
<br>
			<?php echo smarty_function_xl(array('t' => 'URL'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_url(); ?>
<br>
			<?php echo smarty_function_xl(array('t' => 'Size'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_size(); ?>
<br>
			<?php echo smarty_function_xl(array('t' => 'Date'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_date(); ?>
<br>
			<?php echo smarty_function_xl(array('t' => 'Hash'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_hash(); ?>
<br>
			<?php echo smarty_function_xl(array('t' => 'MimeType'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_mimetype(); ?>
<br>
			<?php echo smarty_function_xl(array('t' => 'Revision'), $this);?>
: <?php echo $this->_tpl_vars['file']->revision; ?>
<br><br>
		</div>
	<?php endforeach; unset($_from); endif; ?>
<?php endif; ?>
