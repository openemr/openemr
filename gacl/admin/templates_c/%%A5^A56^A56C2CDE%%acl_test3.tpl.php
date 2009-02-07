<?php /* Smarty version 2.6.14, created on 2009-02-06 04:14:20
         compiled from phpgacl/acl_test3.tpl */ ?>
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
<form method="post" name="acl_list" action="acl_list.php">
<table cellpadding="2" cellspacing="2" border="2" width="100%">
  <tr class="pager">
	<td colspan="12">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/pager.tpl", 'smarty_include_vars' => array('pager_data' => $this->_tpl_vars['paging_data'],'link' => "?")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</td>
  </tr>
  <tr>
    <th>#</th>
    <th>Section > ACO</th>
    <th>Section > ARO</th>
    <th>Section > AXO</th>
    <th>Return Value</th>
    <th>ACL_CHECK() Code</th>
    <th>Debug</th>
    <th>Time (ms)</th>
    <th>Access</th>
  </tr>
  <?php unset($this->_sections['x']);
$this->_sections['x']['name'] = 'x';
$this->_sections['x']['loop'] = is_array($_loop=$this->_tpl_vars['acls']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['x']['show'] = true;
$this->_sections['x']['max'] = $this->_sections['x']['loop'];
$this->_sections['x']['step'] = 1;
$this->_sections['x']['start'] = $this->_sections['x']['step'] > 0 ? 0 : $this->_sections['x']['loop']-1;
if ($this->_sections['x']['show']) {
    $this->_sections['x']['total'] = $this->_sections['x']['loop'];
    if ($this->_sections['x']['total'] == 0)
        $this->_sections['x']['show'] = false;
} else
    $this->_sections['x']['total'] = 0;
if ($this->_sections['x']['show']):

            for ($this->_sections['x']['index'] = $this->_sections['x']['start'], $this->_sections['x']['iteration'] = 1;
                 $this->_sections['x']['iteration'] <= $this->_sections['x']['total'];
                 $this->_sections['x']['index'] += $this->_sections['x']['step'], $this->_sections['x']['iteration']++):
$this->_sections['x']['rownum'] = $this->_sections['x']['iteration'];
$this->_sections['x']['index_prev'] = $this->_sections['x']['index'] - $this->_sections['x']['step'];
$this->_sections['x']['index_next'] = $this->_sections['x']['index'] + $this->_sections['x']['step'];
$this->_sections['x']['first']      = ($this->_sections['x']['iteration'] == 1);
$this->_sections['x']['last']       = ($this->_sections['x']['iteration'] == $this->_sections['x']['total']);
?>
  <tr>
    <td valign="middle" align="center">
		<?php echo $this->_sections['x']['iteration']; ?>

    </td>
    <td valign="middle" align="center">
		<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['display_aco_name']; ?>

    </td>
    <td valign="top" align="left">
        <?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aro_section_name']; ?>
 > <?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aro_name']; ?>

    </td>
    <td valign="top" align="left">
        <?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['axo_section_name']; ?>
 > <?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['axo_name']; ?>

    </td>
    <td valign="top" align="center">
        <?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['return_value']; ?>
<br>
     </td>
    <td valign="top" align="left">
		<!---acl_check('<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aco_section_value']; ?>
', '<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aco_value']; ?>
', '<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aro_section_value']; ?>
', '<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aro_value']; ?>
')-->
		<!---meinhard_jahn@web.de, 20041102: axo_section_value and axo_value implemented--->
		acl_check('<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aco_section_value']; ?>
', '<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aco_value']; ?>
', '<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aro_section_value']; ?>
', '<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aro_value']; ?>
', '<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['axo_section_value']; ?>
', '<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['axo_value']; ?>
')
    </td>  
    <td valign="top" align="center" nowrap>
		 <!---[ <a href="acl_debug.php?aco_section_value=<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aco_section_value']; ?>
&aco_value=<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aco_value']; ?>
&aro_section_value=<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aro_section_value']; ?>
&aro_value=<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aro_value']; ?>
&action=Submit">debug</a> ]-->
		 <!---meinhard_jahn@web.de, 20041102: axo_section_value and axo_value implemented--->
		 [ <a href="acl_debug.php?aco_section_value=<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aco_section_value']; ?>
&aco_value=<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aco_value']; ?>
&aro_section_value=<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aro_section_value']; ?>
&aro_value=<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['aro_value']; ?>
&axo_section_value=<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['axo_section_value']; ?>
&axo_value=<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['axo_value']; ?>
&action=Submit">debug</a> ]
    </td>  
    <td valign="top" align="center">
		<?php echo $this->_tpl_vars['acls'][$this->_sections['x']['index']]['acl_check_time']; ?>

    </td>
    <td valign="middle" class="<?php if ($this->_tpl_vars['acls'][$this->_sections['x']['index']]['access']): ?>green<?php else: ?>red<?php endif; ?>" align="center">
		<?php if ($this->_tpl_vars['acls'][$this->_sections['x']['index']]['access']): ?>
			ALLOW
		<?php else: ?>
			DENY
		<?php endif; ?>
    </td>
  </tr>
  <?php endfor; endif; ?>
  <tr classs="pager">
	<td colspan="12">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/pager.tpl", 'smarty_include_vars' => array('pager_data' => $this->_tpl_vars['paging_data'],'link' => "?")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</td>
  </tr>
</table>
</form>

<br>
<table align="center" cellpadding="2" cellspacing="2" border="2" width="30%">
  <tr>
	<th colspan="2">
		Summary
	</th>
  </tr>
  <tr align="center">
	<td>
		<b>Total ACL Check(s)</b>
	</td>
	<td>
		<?php echo $this->_tpl_vars['total_acl_checks']; ?>

	</td>
  </tr>
  <tr align="center">
	<td>
		<b>Average Time / Check</b>
	</td>
	<td>
		<?php echo $this->_tpl_vars['avg_acl_check_time']; ?>
ms
	</td>
  </tr>
</table>
<br>
<table align="center" cellpadding="2" cellspacing="2" border="2" width="30%">
	<th>
		Do you want to test 2-dimensional ACLs?
	</th>
	<tr align="center">
		<td>
			[ <a href="acl_test2.php">2-dimensional ACLs</a> ]
		</td>
	</tr>
</table>
<br>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>