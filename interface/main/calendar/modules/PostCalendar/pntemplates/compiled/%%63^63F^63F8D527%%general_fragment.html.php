<?php /* Smarty version 2.6.30, created on 2017-08-19 20:41:08
         compiled from /Users/alfiecarlisle/Documents/openemr/templates/prescription/general_fragment.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', '/Users/alfiecarlisle/Documents/openemr/templates/prescription/general_fragment.html', 4, false),array('modifier', 'escape', '/Users/alfiecarlisle/Documents/openemr/templates/prescription/general_fragment.html', 10, false),)), $this); ?>
<table>
  <?php if (empty ( $this->_tpl_vars['prescriptions'] )): ?>
        <tr class='text'>
                <td>&nbsp;&nbsp;<?php echo smarty_function_xl(array('t' => 'None'), $this);?>
</td>
        </tr>
  <?php endif; ?>
	<?php $_from = $this->_tpl_vars['prescriptions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['prescription']):
?>
  <?php if ($this->_tpl_vars['prescription']->get_active() > 0): ?>
	<tr class='text'>
		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->drug)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
		<td><?php echo $this->_tpl_vars['prescription']->get_dosage_display(); ?>
</td>
	</tr>
  <?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
</table>