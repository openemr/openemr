{include file="phpgacl/header.tpl"} 
  </head>
<body>
{include file="phpgacl/navigation.tpl"}
<form method="post" name="acl_list" action="acl_list.php">
<table cellpadding="2" cellspacing="2" border="2" width="100%">
  <tr class="pager">
	<td colspan="12">
		{include file="phpgacl/pager.tpl" pager_data=$paging_data link="?"}
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
  {section name=x loop=$acls}
  <tr>
    <td valign="middle" align="center">
		{$smarty.section.x.iteration}
    </td>
    <td valign="middle" align="center">
		{$acls[x].display_aco_name}
    </td>
    <td valign="top" align="left">
        {$acls[x].aro_section_name} > {$acls[x].aro_name}
    </td>
    <td valign="top" align="left">
        {$acls[x].axo_section_name} > {$acls[x].axo_name}
    </td>
    <td valign="top" align="center">
        {$acls[x].return_value}<br>
     </td>
    <td valign="top" align="left">
		<!---acl_check('{$acls[x].aco_section_value}', '{$acls[x].aco_value}', '{$acls[x].aro_section_value}', '{$acls[x].aro_value}')-->
		<!---meinhard_jahn@web.de, 20041102: axo_section_value and axo_value implemented--->
		acl_check('{$acls[x].aco_section_value}', '{$acls[x].aco_value}', '{$acls[x].aro_section_value}', '{$acls[x].aro_value}', '{$acls[x].axo_section_value}', '{$acls[x].axo_value}')
    </td>  
    <td valign="top" align="center" nowrap>
		 <!---[ <a href="acl_debug.php?aco_section_value={$acls[x].aco_section_value}&aco_value={$acls[x].aco_value}&aro_section_value={$acls[x].aro_section_value}&aro_value={$acls[x].aro_value}&action=Submit">debug</a> ]-->
		 <!---meinhard_jahn@web.de, 20041102: axo_section_value and axo_value implemented--->
		 [ <a href="acl_debug.php?aco_section_value={$acls[x].aco_section_value}&aco_value={$acls[x].aco_value}&aro_section_value={$acls[x].aro_section_value}&aro_value={$acls[x].aro_value}&axo_section_value={$acls[x].axo_section_value}&axo_value={$acls[x].axo_value}&action=Submit">debug</a> ]
    </td>  
    <td valign="top" align="center">
		{$acls[x].acl_check_time}
    </td>
    <td valign="middle" class="{if $acls[x].access}green{else}red{/if}" align="center">
		{if $acls[x].access}
			ALLOW
		{else}
			DENY
		{/if}
    </td>
  </tr>
  {/section}
  <tr classs="pager">
	<td colspan="12">
		{include file="phpgacl/pager.tpl" pager_data=$paging_data link="?"}
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
		{$total_acl_checks}
	</td>
  </tr>
  <tr align="center">
	<td>
		<b>Average Time / Check</b>
	</td>
	<td>
		{$avg_acl_check_time}ms
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
{include file="phpgacl/footer.tpl"}