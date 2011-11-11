<?php /* Smarty version 2.3.1, created on 2011-11-11 12:56:40
         compiled from default/views/day/default.html */ ?>

<?php 
 $this->assign('cal_ui', $_SESSION['cal_ui']);
 ?>
<?php if ($this->_tpl_vars['cal_ui'] == 2): ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include("$TPL_NAME/views/day/fancy_template.html", array());
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php elseif ($this->_tpl_vars['cal_ui'] == 3): ?>
    <?php if ($this->_tpl_vars['PRINT_VIEW'] == 1): ?>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include("$TPL_NAME/views/day_print/outlook_ajax_template.html", array());
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php else: ?>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include("$TPL_NAME/views/day/ajax_template.html", array());
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endif; ?>
<?php else: ?> 
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include("$TPL_NAME/views/day/orig_default.html", array());
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>