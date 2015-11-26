<?php /* Smarty version 2.6.2, created on 2015-11-20 16:50:39
         compiled from /var/www/html/ppemr/templates/pharmacies/general_list.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', '/var/www/html/ppemr/templates/pharmacies/general_list.html', 2, false),array('modifier', 'upper', '/var/www/html/ppemr/templates/pharmacies/general_list.html', 16, false),)), $this); ?>
<a href="controller.php?practice_settings&<?php echo $this->_tpl_vars['TOP_ACTION']; ?>
pharmacy&action=edit" <?php  if (!$GLOBALS['concurrent_layout']) echo "target='Main'";  ?> onclick="top.restoreSession()" class="css_button" >
<span><?php echo smarty_function_xl(array('t' => 'Add a Pharmacy'), $this);?>
</span></a><br><br>

<table cellpadding="1" cellspacing='0' class="showborder">
	<tr class="showborder_head">
		<th width="170px"><b><?php echo smarty_function_xl(array('t' => 'Name'), $this);?>
</b></th>
		<th width="320px"><b><?php echo smarty_function_xl(array('t' => 'Address'), $this);?>
</b></th>
		<th><b><?php echo smarty_function_xl(array('t' => 'Default Method'), $this);?>
</b></th>
	</tr>
	<?php if (count($_from = (array)$this->_tpl_vars['pharmacies'])):
    foreach ($_from as $this->_tpl_vars['pharmacy']):
?>
	<tr height="22">
		<td><a href="<?php echo $this->_tpl_vars['CURRENT_ACTION']; ?>
action=edit&id=<?php echo $this->_tpl_vars['pharmacy']->id; ?>
" onclick="top.restoreSession()"><?php echo $this->_tpl_vars['pharmacy']->name; ?>
&nbsp;</a></td>
		<td>
		<?php if ($this->_tpl_vars['pharmacy']->address->line1 != ''):  echo $this->_tpl_vars['pharmacy']->address->line1; ?>
, <?php endif; ?>
		<?php if ($this->_tpl_vars['pharmacy']->address->city != ''):  echo $this->_tpl_vars['pharmacy']->address->city; ?>
, <?php endif; ?>
			<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->state)) ? $this->_run_mod_handler('upper', true, $_tmp) : smarty_modifier_upper($_tmp)); ?>
 <?php echo $this->_tpl_vars['pharmacy']->address->zip; ?>
&nbsp;</td>
		<td><?php echo $this->_tpl_vars['pharmacy']->get_transmit_method_display(); ?>
&nbsp;
	<?php endforeach; unset($_from); else: ?></td>
	</tr>

	<tr class="center_display">
		<td colspan="3"><b><?php echo smarty_function_xl(array('t' => 'No Pharmacies Found'), $this);?>
<b></td>
	</tr>
	<?php endif; ?>
</table>