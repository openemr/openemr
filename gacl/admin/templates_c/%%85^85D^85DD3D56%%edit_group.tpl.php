<?php /* Smarty version 2.6.14, created on 2009-02-06 04:36:17
         compiled from phpgacl/edit_group.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'phpgacl/edit_group.tpl', 25, false),array('function', 'html_options', 'phpgacl/edit_group.tpl', 28, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <style type="text/css">
    <?php echo '
      select {
        margin-top: 0px;
      }
      input.group-name, input.group-value {
        width: 99%;
      }
    '; ?>

    </style>
  </head>
  <body>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/navigation.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <form method="post" name="edit_group" action="edit_group.php">
      <table cellpadding="2" cellspacing="2" border="2" width="100%">
        <tbody>
          <tr>
            <th width="4%">ID</th>
            <th width="32%">Parent</th>
            <th width="32%">Name</th>
            <th width="32%">Value</th>
          </tr>
          <tr valign="top">
            <td align="center"><?php echo ((is_array($_tmp=@$this->_tpl_vars['id'])) ? $this->_run_mod_handler('default', true, $_tmp, "N/A") : smarty_modifier_default($_tmp, "N/A")); ?>
</td>
            <td>
                <select name="parent_id" tabindex="0" multiple>
                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['options_groups'],'selected' => $this->_tpl_vars['parent_id']), $this);?>

                </select>
            </td>
            <td>
                <input type="text" class="group-name" size="50" name="name" value="<?php echo $this->_tpl_vars['name']; ?>
">
            </td>
            <td>
                <input type="text" class="group-value" size="50" name="value" value="<?php echo $this->_tpl_vars['value']; ?>
">
            </td>
          </tr>
          <tr class="controls" align="center">
            <td colspan="4">
              <input type="submit" class="button" name="action" value="Submit"> <input type="reset" class="button" value="Reset">
            </td>
          </tr>
        </tbody>
      </table>
    <input type="hidden" name="group_id" value="<?php echo $this->_tpl_vars['id']; ?>
">
    <input type="hidden" name="group_type" value="<?php echo $this->_tpl_vars['group_type']; ?>
">
    <input type="hidden" name="return_page" value="<?php echo $this->_tpl_vars['return_page']; ?>
">
  </form>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>