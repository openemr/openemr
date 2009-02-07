<?php /* Smarty version 2.6.14, created on 2009-02-06 03:21:01
         compiled from phpgacl/acl_debug.tpl */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> 
  </head>
<body>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/navigation.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<form method="get" name="acl_debug" action="acl_debug.php">
<table cellpadding="2" cellspacing="2" border="2" width="100%">
  <tr>
  	<th rowspan="2">&nbsp;</th>
  	<th colspan="2">ACO</th>
  	<th colspan="2">ARO</th>
  	<th colspan="2">AXO</th>
    <th rowspan="2">Root ARO<br />Group ID</th>
    <th rowspan="2">Root AXO<br />Group ID</th>
    <th rowspan="2">&nbsp;</th>
  </tr>
  <tr>
    <th>Section</th>
    <th>Value</th>
    <th>Section</th>
    <th>Value</th>
    <th>Section</th>
    <th>Value</th>
  </tr>
  <tr valign="middle" align="center">
    <td nowrap><b>acl_query(</b></td>
    <td><input type="text" name="aco_section_value" size="15" value="<?php echo $this->_tpl_vars['aco_section_value']; ?>
"></td>
    <td><input type="text" name="aco_value" size="15" value="<?php echo $this->_tpl_vars['aco_value']; ?>
"></td>
    <td><input type="text" name="aro_section_value" size="15" value="<?php echo $this->_tpl_vars['aro_section_value']; ?>
"></td>
    <td><input type="text" name="aro_value" size="15" value="<?php echo $this->_tpl_vars['aro_value']; ?>
"></td>
    <td><input type="text" name="axo_section_value" size="15" value="<?php echo $this->_tpl_vars['axo_section_value']; ?>
"></td>
    <td><input type="text" name="axo_value" size="15" value="<?php echo $this->_tpl_vars['axo_value']; ?>
"></td>
    <td><input type="text" name="root_aro_group_id" size="15" value="<?php echo $this->_tpl_vars['root_aro_group_id']; ?>
"></td>
    <td><input type="text" name="root_axo_group_id" size="15" value="<?php echo $this->_tpl_vars['root_axo_group_id']; ?>
"></td>
    <td><b>)</b></td>
  </tr>
  <tr class="controls" align="center">
    <td colspan="10">
    	<input type="submit" class="button" name="action" value="Submit">
    </td>
  </tr>
</table>
<?php if (count ( $this->_tpl_vars['acls'] ) > 0): ?>
<br />
<table cellpadding="2" cellspacing="2" border="2" width="100%">
  <tr>
    <th rowspan="2" width="4%">ACL ID</th>
    <th colspan="2">ACO</th>
    <th colspan="2">ARO</th>
    <th colspan="2">AXO</th>
    <th colspan="2">ACL</th>
  </tr>
  <tr>
    <th width="12%">Section</th>
    <th width="12%">Value</th>
    <th width="12%">Section</th>
    <th width="12%">Value</th>
    <th width="12%">Section</th>
    <th width="12%">Value</th>
    <th width="8%">Access</th>
    <th width="16%">Updated Date</th>
  </tr>
<?php $_from = $this->_tpl_vars['acls']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['acl']):
?>
  <tr valign="top" align="left">
    <td valign="middle" rowspan="2" align="center">
        <?php echo $this->_tpl_vars['acl']['id']; ?>

    </td>
    <td nowrap>
		<?php echo $this->_tpl_vars['acl']['aco_section_value']; ?>

    </td>
    <td nowrap>
		<?php echo $this->_tpl_vars['acl']['aco_value']; ?>

    </td>

    <td nowrap>
		<?php echo $this->_tpl_vars['acl']['aro_section_value']; ?>
<br>
    </td>
    <td nowrap>
		<?php echo $this->_tpl_vars['acl']['aro_value']; ?>
<br>
    </td>

    <td nowrap>
		<?php echo $this->_tpl_vars['acl']['axo_section_value']; ?>
<br>
    </td>
    <td nowrap>
		<?php echo $this->_tpl_vars['acl']['axo_value']; ?>
<br>
    </td>

    <td valign="middle" class="<?php if ($this->_tpl_vars['acl']['allow']): ?>green<?php else: ?>red<?php endif; ?>" align="center">
		<?php if ($this->_tpl_vars['acl']['allow']): ?>
			ALLOW
		<?php else: ?>
			DENY
		<?php endif; ?>
    </td>
    <td valign="middle" align="center">
        <?php echo $this->_tpl_vars['acl']['updated_date']; ?>

     </td>
  </tr>
  <tr valign="middle" align="left">
    <td colspan="4">
        <b>Return Value:</b> <?php echo $this->_tpl_vars['acl']['return_value']; ?>
<br>
    </td>
    <td colspan="4">
        <b>Note:</b> <?php echo $this->_tpl_vars['acl']['note']; ?>

    </td>
  </tr>
<?php endforeach; endif; unset($_from); ?>
</table>
<?php endif; ?>
<input type="hidden" name="return_page" value="<?php echo $this->_tpl_vars['return_page']; ?>
">
</form>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>