<?php /* Smarty version 2.6.14, created on 2009-02-05 19:08:03
         compiled from phpgacl/acl_list.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'phpgacl/acl_list.tpl', 57, false),array('function', 'cycle', 'phpgacl/acl_list.tpl', 127, false),array('modifier', 'date_format', 'phpgacl/acl_list.tpl', 226, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/acl_admin_js.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  echo '
    <style type="text/css">
	ul {
		padding: 0px 0px 0px 0px;
		margin: 0px 0px 0px 0px;
		list-style-type: none;
	}
	ul li {
		padding: 0px;
		margin: 0px;
		font-weight: bold;
	}
	ol {
		padding: 0px 0px 0px 22px;
		margin: 0px;
	}
	ol li {
		padding: 0px;
		margin: 0px;
		font-weight: normal;
	}
	div.divider {
		margin: 2px 0px;
		padding: 0px;
		border-bottom: 1px solid grey;
	}
	input.filter {
		width: 99%;
	}
	select.filter {
		width: 99%;
		margin-top: 0px;
	}
   </style>
'; ?>

  </head>
<body>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/navigation.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<form method="get" name="acl_list" action="acl_list.php">
<table cellpadding="2" cellspacing="2" border="2" width="100%">
  <tr align="center">
    <td colspan="6"><b>Filter</b></td>
  </tr>
  <tr>
    <th width="12%">&nbsp;</th>
    <th width="22%">ACO</th>
    <th width="22%">ARO</th>
    <th width="22%">AXO</th>
    <th width="22%" colspan="2">ACL</th>
  </tr>
  <tr valign="middle" align="center">
    <td align="left"><b>Section:</b> </td>
    <td>
		<select name="filter_aco_section" tabindex="0" class="filter">
			<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['options_filter_aco_sections'],'selected' => $this->_tpl_vars['filter_aco_section']), $this);?>

		</select>
    </td>
    <td>
		<select name="filter_aro_section" tabindex="0" class="filter">
			<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['options_filter_aro_sections'],'selected' => $this->_tpl_vars['filter_aro_section']), $this);?>

		</select>
    </td>
    <td>
		<select name="filter_axo_section" tabindex="0" class="filter">
			<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['options_filter_axo_sections'],'selected' => $this->_tpl_vars['filter_axo_section']), $this);?>

		</select>
    </td>
    <td colspan="2">
		<select name="filter_acl_section" tabindex="0" class="filter">
			<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['options_filter_acl_sections'],'selected' => $this->_tpl_vars['filter_acl_section']), $this);?>

		</select>
    </td>
  </tr>
  <tr valign="middle" align="center">
    <td align="left"><b>Object:</b> </td>
    <td><input type="text" name="filter_aco" size="20" value="<?php echo $this->_tpl_vars['filter_aco']; ?>
" class="filter"></td>
    <td><input type="text" name="filter_aro" size="20" value="<?php echo $this->_tpl_vars['filter_aro']; ?>
" class="filter"></td>
    <td><input type="text" name="filter_axo" size="20" value="<?php echo $this->_tpl_vars['filter_axo']; ?>
" class="filter"></td>
    <td align="left" width="11%"><b>Allow:</b> </td>
    <td align="left" width="11%">
		 <select name="filter_allow" tabindex="0" class="filter">
			<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['options_filter_allow'],'selected' => $this->_tpl_vars['filter_allow']), $this);?>

		</select>
    </td>
  </tr>
  <tr valign="middle" align="center">
    <td align="left"><b>Group:</b> </td>
    <td>&nbsp;</td>
    <td><input type="text" name="filter_aro_group" size="20" value="<?php echo $this->_tpl_vars['filter_aro_group']; ?>
" class="filter"></td>
    <td><input type="text" name="filter_axo_group" size="20" value="<?php echo $this->_tpl_vars['filter_axo_group']; ?>
" class="filter"></td>
    <td align="left"><b>Enabled:</b> </td>
    <td align="left">
		<select name="filter_enabled" tabindex="0" class="filter">
			<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['options_filter_enabled'],'selected' => $this->_tpl_vars['filter_enabled']), $this);?>

		</select>
    </td>
  </tr>
  <tr valign="middle" align="left">
	<td><b>Return&nbsp;Value:</b> </td>
	<td colspan="5"><input type="text" name="filter_return_value" size="50" value="<?php echo $this->_tpl_vars['filter_return_value']; ?>
" class="filter"></td>
  </tr>
  <tr class="controls" align="center">
    <td colspan="6"><input type="submit" class="button" name="action" value="Filter"></td>
  </tr>
</table>
<br />
<table cellpadding="2" cellspacing="2" border="2" width="100%">
  <tr class="pager">
	<td colspan="8">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/pager.tpl", 'smarty_include_vars' => array('pager_data' => $this->_tpl_vars['paging_data'],'link' => "?action=".($this->_tpl_vars['action'])."&filter_aco_section=".($this->_tpl_vars['filter_aco_section'])."&filter_aco=".($this->_tpl_vars['filter_aco'])."&filter_aro_section=".($this->_tpl_vars['filter_aro_section'])."&filter_aro=".($this->_tpl_vars['filter_aro'])."&filter_axo_section=".($this->_tpl_vars['filter_axo_section'])."&filter_axo=".($this->_tpl_vars['filter_axo'])."&filter_aro_group=".($this->_tpl_vars['filter_aro_group'])."&filter_axo_group=".($this->_tpl_vars['filter_axo_group'])."&filter_return_value=".($this->_tpl_vars['filter_return_value'])."&filter_allow=".($this->_tpl_vars['filter_allow'])."&filter_enabled=".($this->_tpl_vars['filter_enabled'])."&")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</td>
  </tr>
  <tr>
    <th width="2%">ID</th>
    <th width="24%">ACO</th>
    <th width="24%">ARO</th>
    <th width="24%">AXO</th>
    <th width="10%">Access</th>
    <th width="10%">Enabled</th>
    <th width="4%">Functions</th>
    <th width="2%"><input type="checkbox" class="checkbox" name="select_all" onClick="checkAll(this)"/></th>
  </tr>

<?php $_from = $this->_tpl_vars['acls']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['acl']):
?>
  <?php echo smarty_function_cycle(array('assign' => 'class','values' => "odd,even"), $this);?>

  <tr class="<?php echo $this->_tpl_vars['class']; ?>
">
    <td valign="middle" rowspan="3" align="center"><?php echo $this->_tpl_vars['acl']['id']; ?>
</td>
    <td valign="top" align="left">
	<?php if (count ( $this->_tpl_vars['acl']['aco'] ) > 0): ?>
		<ul>
		<?php $_from = $this->_tpl_vars['acl']['aco']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['section'] => $this->_tpl_vars['objects']):
?>
			<li><?php echo $this->_tpl_vars['section']; ?>
<ol>
			<?php $_from = $this->_tpl_vars['objects']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['obj']):
?>
				<li><?php echo $this->_tpl_vars['obj']; ?>
</li>
			<?php endforeach; endif; unset($_from); ?>
			</ol></li>
		<?php endforeach; endif; unset($_from); ?>
		</ul>
	<?php else: ?>
		&nbsp;
	<?php endif; ?>
    </td>
    <td valign="top" align="left">
	  <?php if (count ( $this->_tpl_vars['acl']['aro'] ) > 0): ?>
		<ul>
		  <?php $_from = $this->_tpl_vars['acl']['aro']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['section'] => $this->_tpl_vars['objects']):
?>
			<li><?php echo $this->_tpl_vars['section']; ?>
<ol>
			<?php $_from = $this->_tpl_vars['objects']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['obj']):
?>
				<li><?php echo $this->_tpl_vars['obj']; ?>
</li>
			<?php endforeach; endif; unset($_from); ?>
			</ol></li>
		  <?php endforeach; endif; unset($_from); ?>
		</ul>
		<?php if (count ( $this->_tpl_vars['acl']['aro_groups'] ) > 0): ?>
		<div class="divider"></div>
		<?php endif; ?>
	  <?php endif; ?>
	  <?php if (count ( $this->_tpl_vars['acl']['aro_groups'] ) > 0): ?>
		<b>Groups</b><ol>
		  <?php $_from = $this->_tpl_vars['acl']['aro_groups']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['group']):
?>
			<li><?php echo $this->_tpl_vars['group']; ?>
</li>
		  <?php endforeach; endif; unset($_from); ?>
		</ol>
	  <?php endif; ?>
    </td>
    <td valign="top" align="left">
	  <?php if (count ( $this->_tpl_vars['acl']['axo'] ) > 0): ?>
		<ul>
		  <?php $_from = $this->_tpl_vars['acl']['axo']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['section'] => $this->_tpl_vars['objects']):
?>
			<li><?php echo $this->_tpl_vars['section']; ?>
<ol>
			<?php $_from = $this->_tpl_vars['objects']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['obj']):
?>
				<li><?php echo $this->_tpl_vars['obj']; ?>
</li>
			<?php endforeach; endif; unset($_from); ?>
			</ol></li>
		  <?php endforeach; endif; unset($_from); ?>
		</ul>
		<?php if (count ( $this->_tpl_vars['acl']['axo_groups'] ) > 0): ?>
		<div class="divider"></div>
		<?php endif; ?>
	  <?php endif; ?>
	  <?php if (count ( $this->_tpl_vars['acl']['axo_groups'] ) > 0): ?>
		<b>Groups</b><ol>
		  <?php $_from = $this->_tpl_vars['acl']['axo_groups']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['group']):
?>
			<li><?php echo $this->_tpl_vars['group']; ?>
</li>
		  <?php endforeach; endif; unset($_from); ?>
		</ol>
	  <?php endif; ?>
    </td>
    <td valign="middle" class="<?php if ($this->_tpl_vars['acl']['allow']): ?>green<?php else: ?>red<?php endif; ?>" align="center">
		<?php if ($this->_tpl_vars['acl']['allow']): ?>
			ALLOW
		<?php else: ?>
			DENY
		<?php endif; ?>
    </td>
    <td valign="middle" class="<?php if ($this->_tpl_vars['acl']['enabled']): ?>green<?php else: ?>red<?php endif; ?>" align="center">
		<?php if ($this->_tpl_vars['acl']['enabled']): ?>
			Yes
		<?php else: ?>
			No
		<?php endif; ?>
    </td>
    <td valign="middle" rowspan="3" align="center">
        [ <a href="acl_admin.php?action=edit&acl_id=<?php echo $this->_tpl_vars['acl']['id']; ?>
&return_page=<?php echo $this->_tpl_vars['return_page']; ?>
">Edit</a> ]
    </td>
    <td valign="middle" rowspan="3" align="center">
        <input type="checkbox" class="checkbox" name="delete_acl[]" value="<?php echo $this->_tpl_vars['acl']['id']; ?>
">
    </td>
  </tr>

  <tr class="<?php echo $this->_tpl_vars['class']; ?>
">
    <td valign="top" colspan="3" align="left">
        <b>Return Value:</b> <?php echo $this->_tpl_vars['acl']['return_value']; ?>

    </td>
    <td valign="middle" colspan="2" align="center">
        <?php echo $this->_tpl_vars['acl']['section_name']; ?>

    </td>
  </tr>
  <tr class="<?php echo $this->_tpl_vars['class']; ?>
">
    <td valign="top" colspan="3" align="left">
        <b>Note:</b> <?php echo $this->_tpl_vars['acl']['note']; ?>

    </td>
    <td valign="middle" colspan="2" align="center">
        <?php echo ((is_array($_tmp=$this->_tpl_vars['acl']['updated_date'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d-%b-%Y&nbsp;%H:%M:%S") : smarty_modifier_date_format($_tmp, "%d-%b-%Y&nbsp;%H:%M:%S")); ?>

    </td>
  </tr>
<?php endforeach; endif; unset($_from); ?>
  <tr class="pager">
	<td colspan="8">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/pager.tpl", 'smarty_include_vars' => array('pager_data' => $this->_tpl_vars['paging_data'],'link' => "?action=".($this->_tpl_vars['action'])."&filter_aco_section=".($this->_tpl_vars['filter_aco_section'])."&filter_aco=".($this->_tpl_vars['filter_aco'])."&filter_aro_section=".($this->_tpl_vars['filter_aro_section'])."&filter_aro=".($this->_tpl_vars['filter_aro'])."&filter_axo_section=".($this->_tpl_vars['filter_axo_section'])."&filter_axo=".($this->_tpl_vars['filter_axo'])."&filter_aro_group=".($this->_tpl_vars['filter_aro_group'])."&filter_axo_group=".($this->_tpl_vars['filter_axo_group'])."&filter_return_value=".($this->_tpl_vars['filter_return_value'])."&filter_allow=".($this->_tpl_vars['filter_allow'])."&filter_enabled=".($this->_tpl_vars['filter_enabled'])."&")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</td>
  </tr>
  <tr class="controls">
    <td colspan="6">&nbsp;</td>
    <td colspan="2" align="center">
      <input type="submit" class="button" name="action" value="Delete">
    </td>
  </tr>
</table>
<input type="hidden" name="return_page" value="<?php echo $this->_tpl_vars['return_page']; ?>
">
</form>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>