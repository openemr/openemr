{include file="phpgacl/header.tpl"}
  </head>
<body>
{include file="phpgacl/navigation.tpl"}
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
    <td><input type="text" name="aco_section_value" size="15" value="{$aco_section_value|attr}"></td>
    <td><input type="text" name="aco_value" size="15" value="{$aco_value|attr}"></td>
    <td><input type="text" name="aro_section_value" size="15" value="{$aro_section_value|attr}"></td>
    <td><input type="text" name="aro_value" size="15" value="{$aro_value|attr}"></td>
    <td><input type="text" name="axo_section_value" size="15" value="{$axo_section_value|attr}"></td>
    <td><input type="text" name="axo_value" size="15" value="{$axo_value|attr}"></td>
    <td><input type="text" name="root_aro_group_id" size="15" value="{$root_aro_group_id|attr}"></td>
    <td><input type="text" name="root_axo_group_id" size="15" value="{$root_axo_group_id|attr}"></td>
    <td><b>)</b></td>
  </tr>
  <tr class="controls" align="center">
    <td colspan="10">
    	<input type="submit" class="button" name="action" value="Submit">
    </td>
  </tr>
</table>
{if is_array($acls) && count($acls) gt 0}
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
{foreach from=$acls item=acl}
  <tr valign="top" align="left">
    <td valign="middle" rowspan="2" align="center">
        {$acl.id|text}
    </td>
    <td nowrap>
		{$acl.aco_section_value|text}
    </td>
    <td nowrap>
		{$acl.aco_value|text}
    </td>

    <td nowrap>
		{$acl.aro_section_value|text}<br />
    </td>
    <td nowrap>
		{$acl.aro_value|text}<br />
    </td>

    <td nowrap>
		{$acl.axo_section_value|text}<br />
    </td>
    <td nowrap>
		{$acl.axo_value|text}<br />
    </td>

    <td valign="middle" class="{if $acl.allow}green{else}red{/if}" align="center">
		{if $acl.allow}
			ALLOW
		{else}
			DENY
		{/if}
    </td>
    <td valign="middle" align="center">
        {$acl.updated_date|text}
     </td>
  </tr>
  <tr valign="middle" align="left">
    <td colspan="4">
        <b>Return Value:</b> {$acl.return_value|text}<br />
    </td>
    <td colspan="4">
        <b>Note:</b> {$acl.note|text}
    </td>
  </tr>
{/foreach}
</table>
{/if}
<input type="hidden" name="return_page" value="{$return_page|attr}">
</form>
{include file="phpgacl/footer.tpl"}
