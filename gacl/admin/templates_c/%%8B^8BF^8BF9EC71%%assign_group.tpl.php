<?php /* Smarty version 2.6.14, created on 2009-02-05 20:08:58
         compiled from phpgacl/assign_group.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'phpgacl/assign_group.tpl', 23, false),array('modifier', 'upper', 'phpgacl/assign_group.tpl', 54, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<script LANGUAGE="JavaScript">
<?php echo $this->_tpl_vars['js_array']; ?>

</script>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/acl_admin_js.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
  </head>
  <body onload="populate(document.assign_group.<?php echo $this->_tpl_vars['group_type']; ?>
_section,document.assign_group.elements['objects[]'], '<?php echo $this->_tpl_vars['js_array_name']; ?>
')">
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/navigation.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>    
    <form method="post" name="assign_group" action="assign_group.php">
      <table cellpadding="2" cellspacing="2" border="2" width="100%">
        <tbody>
          <tr>
            <th width="32%">Sections</th>
            <th width="32%"><?php echo $this->_tpl_vars['object_type']; ?>
s</th>
            <th width="4%">&nbsp;</th>
            <th width="32%">Selected</th>
          </tr>
          <tr valign="top" align="center">
            <td>
              [ <a href="edit_object_sections.php?object_type=<?php echo $this->_tpl_vars['group_type']; ?>
&return_page=<?php echo $this->_tpl_vars['return_page']; ?>
">Edit</a> ]
              <br />
              <select name="<?php echo $this->_tpl_vars['group_type']; ?>
_section" tabindex="0" size="10" width="200" onclick="populate(document.assign_group.<?php echo $this->_tpl_vars['group_type']; ?>
_section,document.assign_group.elements['objects[]'],'<?php echo $this->_tpl_vars['js_array_name']; ?>
')">
                <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['options_sections'],'selected' => $this->_tpl_vars['section_value']), $this);?>

              </select>
            </td>
            <td>
              [ <a href="javascript: location.href = 'edit_objects.php?object_type=<?php echo $this->_tpl_vars['group_type']; ?>
&section_value=' + document.assign_group.<?php echo $this->_tpl_vars['group_type']; ?>
_section.options[document.assign_group.<?php echo $this->_tpl_vars['group_type']; ?>
_section.selectedIndex].value + '&return_page=<?php echo $this->_tpl_vars['return_page']; ?>
';">Edit</a> ]
              [ <a href="#" onClick="window.open('object_search.php?src_form=assign_group&object_type=<?php echo $this->_tpl_vars['group_type']; ?>
&section_value=' + document.assign_group.<?php echo $this->_tpl_vars['group_type']; ?>
_section.options[document.assign_group.<?php echo $this->_tpl_vars['group_type']; ?>
_section.selectedIndex].value,'','status=yes,width=400,height=400','','status=yes,width=400,height=400');">Search</a> ]
              <br />
              <select name="objects[]" tabindex="0" size="10" width="200" multiple>
              </select>
            </td>
            <td valign="middle">
              <br /><input type="button" class="select" name="select" value="&nbsp;&gt;&gt;&nbsp;" onClick="select_item(document.assign_group.<?php echo $this->_tpl_vars['group_type']; ?>
_section, document.assign_group.elements['objects[]'], document.assign_group.elements['selected_<?php echo $this->_tpl_vars['group_type']; ?>
[]'])">
              <br /><input type="button" class="deselect" name="deselect" value="&nbsp;&lt;&lt;&nbsp;" onClick="deselect_item(document.assign_group.elements['selected_<?php echo $this->_tpl_vars['group_type']; ?>
[]'])">
            </td>
            <td>
              <br />
              <select name="selected_<?php echo $this->_tpl_vars['group_type']; ?>
[]" tabindex="0" size="10" width="200" multiple>
				<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['options_selected_objects'],'selected' => $this->_tpl_vars['selected_object']), $this);?>

              </select>
            </td>
          </tr>
          <tr class="controls" align="center">
            <td colspan="4">
              <input type="submit" class="button" name="action" value="Submit"> <input type="reset" class="button" value="Reset">
            </td>
          </tr>
        </tbody>
      </table>
      <br />
      <table cellpadding="2" cellspacing="2" border="2" width="100%">
        <tr align="center">
	      <td colspan="5"><b><?php echo $this->_tpl_vars['total_objects']; ?>
</b> <?php echo ((is_array($_tmp=$this->_tpl_vars['group_type'])) ? $this->_run_mod_handler('upper', true, $_tmp) : smarty_modifier_upper($_tmp)); ?>
s in Group: <b><?php echo $this->_tpl_vars['group_name']; ?>
</b></td>
        </tr>
        <tr class="pager">
          <td colspan="5">
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/pager.tpl", 'smarty_include_vars' => array('pager_data' => $this->_tpl_vars['paging_data'],'link' => "?group_type=".($this->_tpl_vars['group_type'])."&group_id=".($this->_tpl_vars['group_id'])."&")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
	<th>Section</th>
	<th><?php echo $this->_tpl_vars['object_type']; ?>
</th>
	<th><?php echo ((is_array($_tmp=$this->_tpl_vars['group_type'])) ? $this->_run_mod_handler('upper', true, $_tmp) : smarty_modifier_upper($_tmp)); ?>
 Value</th>
	<th width="4%">Functions</th>
	<th width="2%"><input type="checkbox" class="checkbox" name="select_all" onClick="checkAll(this)"/></th>
        </tr>
<?php $_from = $this->_tpl_vars['rows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?>
  <tr valign="top" align="center">
    <td>
      <?php echo $this->_tpl_vars['row']['section']; ?>

    </td>
    <td>
      <?php echo $this->_tpl_vars['row']['name']; ?>

    </td>
    <td>
      <?php echo $this->_tpl_vars['row']['value']; ?>

    </td>
    <td>
      [ <a href="acl_list.php?action=Filter&filter_<?php echo $this->_tpl_vars['group_type']; ?>
_section=<?php echo $this->_tpl_vars['row']['section_value']; ?>
&filter_<?php echo $this->_tpl_vars['group_type']; ?>
=<?php echo $this->_tpl_vars['row']['name']; ?>
&return_page=<?php echo $this->_tpl_vars['return_page']; ?>
">ACLs</a> ]
    </td>
    <td>
      <input type="checkbox" class="checkbox" name="delete_assigned_object[]" value="<?php echo $this->_tpl_vars['row']['section_value']; ?>
^<?php echo $this->_tpl_vars['row']['value']; ?>
">
    </td>
  </tr>
<?php endforeach; endif; unset($_from); ?>
  <tr class="pager">
    <td colspan="5">
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/pager.tpl", 'smarty_include_vars' => array('pager_data' => $this->_tpl_vars['paging_data'],'link' => "?")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
  </tr>
  <tr class="controls" align="center">
    <td colspan="3">&nbsp;</td>
    <td colspan="2">
      <input type="submit" class="button" name="action" value="Remove">
    </td>
  </tr>
</table>
<input type="hidden" name="group_id" value="<?php echo $this->_tpl_vars['group_id']; ?>
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