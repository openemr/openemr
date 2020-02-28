<?php /* Smarty version 2.6.31, created on 2020-02-28 16:23:33
         compiled from C:/xampp/htdocs/openemr/templates/prescription/general_fragment.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xlt', 'C:/xampp/htdocs/openemr/templates/prescription/general_fragment.html', 15, false),array('modifier', 'text', 'C:/xampp/htdocs/openemr/templates/prescription/general_fragment.html', 21, false),)), $this); ?>
<table>
    <?php if (empty ( $this->_tpl_vars['prescriptions'] )): ?>
    <tr class='text'>
        <td>&nbsp;&nbsp;<?php echo smarty_function_xlt(array('t' => 'None'), $this);?>
</td>
    </tr>
    <?php endif; ?>
    <?php $_from = $this->_tpl_vars['prescriptions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['prescription']):
?>
    <?php if ($this->_tpl_vars['prescription']->get_active() > 0): ?>
    <tr class='text'>
        <td><?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->drug)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
&nbsp;</td>
        <td><?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->get_size())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->get_unit_display())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
&nbsp;
            <?php echo ((is_array($_tmp=$this->_tpl_vars['prescription']->get_dosage_display())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</td>
        <td>&nbsp;</td>
    </tr>
    <?php endif; ?>
    <?php endforeach; endif; unset($_from); ?>
</table>