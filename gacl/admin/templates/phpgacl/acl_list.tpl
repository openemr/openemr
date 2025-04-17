{include file="phpgacl/header.tpl"}
{include file="phpgacl/acl_admin_js.tpl"}

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

  </head>
<body>
{include file="phpgacl/navigation.tpl"}
<form method="get" name="acl_list" action="acl_list.php">
<input type="hidden" name="csrf_token_form" value="{$CSRF_TOKEN_FORM|attr}">
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
			{html_options options=$options_filter_aco_sections selected=$filter_aco_section_escaped}
		</select>
    </td>
    <td>
		<select name="filter_aro_section" tabindex="0" class="filter">
			{html_options options=$options_filter_aro_sections selected=$filter_aro_section_escaped}
		</select>
    </td>
    <td>
		<select name="filter_axo_section" tabindex="0" class="filter">
			{html_options options=$options_filter_axo_sections selected=$filter_axo_section_escaped}
		</select>
    </td>
    <td colspan="2">
		<select name="filter_acl_section" tabindex="0" class="filter">
			{html_options options=$options_filter_acl_sections selected=$filter_acl_section_escaped}
		</select>
    </td>
  </tr>
  <tr valign="middle" align="center">
    <td align="left"><b>Object:</b> </td>
    <td><input type="text" name="filter_aco" size="20" value="{$filter_aco|attr}" class="filter"></td>
    <td><input type="text" name="filter_aro" size="20" value="{$filter_aro|attr}" class="filter"></td>
    <td><input type="text" name="filter_axo" size="20" value="{$filter_axo|attr}" class="filter"></td>
    <td align="left" width="11%"><b>Allow:</b> </td>
    <td align="left" width="11%">
		 <select name="filter_allow" tabindex="0" class="filter">
			{html_options options=$options_filter_allow selected=$filter_allow}
		</select>
    </td>
  </tr>
  <tr valign="middle" align="center">
    <td align="left"><b>Group:</b> </td>
    <td>&nbsp;</td>
    <td><input type="text" name="filter_aro_group" size="20" value="{$filter_aro_group|attr}" class="filter"></td>
    <td><input type="text" name="filter_axo_group" size="20" value="{$filter_axo_group|attr}" class="filter"></td>
    <td align="left"><b>Enabled:</b> </td>
    <td align="left">
		<select name="filter_enabled" tabindex="0" class="filter">
			{html_options options=$options_filter_enabled selected=$filter_enabled}
		</select>
    </td>
  </tr>
  <tr valign="middle" align="left">
	<td><b>Return&nbsp;Value:</b> </td>
	<td colspan="5"><input type="text" name="filter_return_value" size="50" value="{$filter_return_value|attr}" class="filter"></td>
  </tr>
  <tr class="controls" align="center">
    <td colspan="6"><input type="submit" class="button" name="action" value="Filter"></td>
  </tr>
</table>
<br />
<table cellpadding="2" cellspacing="2" border="2" width="100%">
  <tr class="pager">
	<td colspan="8">
		{include file="phpgacl/pager.tpl" pager_data=$paging_data link="?action=$action_escaped&filter_aco_section=$filter_aco_section_escaped&filter_aco=$filter_aco_escaped&filter_aro_section=$filter_aro_section_escaped&filter_aro=$filter_aro_escaped&filter_axo_section=$filter_axo_section_escaped&filter_axo=$filter_axo_escaped&filter_aro_group=$filter_aro_group_escaped&filter_axo_group=$filter_axo_group_escaped&filter_return_value=$filter_return_value_escaped&filter_allow=$filter_allow_escaped&filter_enabled=$filter_enabled_escaped&"}
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

{foreach from=$acls item=acl}
  {cycle assign=class values="odd,even"}
  <tr class="{$class}">
    <td valign="middle" rowspan="3" align="center">{$acl.id|text}</td>
    <td valign="top" align="left">
	{if count($acl.aco) gt 0}
		<ul>
		{foreach from=$acl.aco key=section item=objects}
			<li>{$section|text}<ol>
			{foreach from=$objects item=obj}
				<li>{$obj|text}</li>
			{/foreach}
			</ol></li>
		{/foreach}
		</ul>
	{else}
		&nbsp;
	{/if}
    </td>
    <td valign="top" align="left">
	  {if count($acl.aro) gt 0}
		<ul>
		  {foreach from=$acl.aro key=section item=objects}
			<li>{$section|text}<ol>
			{foreach from=$objects item=obj}
				<li>{$obj|text}</li>
			{/foreach}
			</ol></li>
		  {/foreach}
		</ul>
		{if count($acl.aro_groups) gt 0}
		<div class="divider"></div>
		{/if}
	  {/if}
	  {if count($acl.aro_groups) gt 0}
		<b>Groups</b><ol>
		  {foreach from=$acl.aro_groups item=group}
			<li>{$group|text}</li>
		  {/foreach}
		</ol>
	  {/if}
    </td>
    <td valign="top" align="left">
	  {if count($acl.axo) gt 0}
		<ul>
		  {foreach from=$acl.axo key=section item=objects}
			<li>{$section|text}<ol>
			{foreach from=$objects item=obj}
				<li>{$obj|text}</li>
			{/foreach}
			</ol></li>
		  {/foreach}
		</ul>
		{if count($acl.axo_groups) gt 0}
		<div class="divider"></div>
		{/if}
	  {/if}
	  {if count($acl.axo_groups) gt 0}
		<b>Groups</b><ol>
		  {foreach from=$acl.axo_groups item=group}
			<li>{$group|text}</li>
		  {/foreach}
		</ol>
	  {/if}
    </td>
    <td valign="middle" class="{if $acl.allow}green{else}red{/if}" align="center">
		{if $acl.allow}
			ALLOW
		{else}
			DENY
		{/if}
    </td>
    <td valign="middle" class="{if $acl.enabled}green{else}red{/if}" align="center">
		{if $acl.enabled}
			Yes
		{else}
			No
		{/if}
    </td>
    <td valign="middle" rowspan="3" align="center">
        [ <a href="acl_admin.php?action=edit&acl_id={$acl.id|attr_url}&csrf_token_form={$CSRF_TOKEN_FORM|attr_url}&return_page={$return_page|attr_url}">Edit</a> ]
    </td>
    <td valign="middle" rowspan="3" align="center">
        <input type="checkbox" class="checkbox" name="delete_acl[]" value="{$acl.id|attr}">
    </td>
  </tr>

  <tr class="{$class}">
    <td valign="top" colspan="3" align="left">
        <b>Return Value:</b> {$acl.return_value|text}
    </td>
    <td valign="middle" colspan="2" align="center">
        {$acl.section_name|text}
    </td>
  </tr>
  <tr class="{$class}">
    <td valign="top" colspan="3" align="left">
        <b>Note:</b> {$acl.note|text}
    </td>
    <td valign="middle" colspan="2" align="center">
        {$acl.updated_date|date_format:"%d-%b-%Y %H:%M:%S"|text}
    </td>
  </tr>
{/foreach}
  <tr class="pager">
	<td colspan="8">
		{include file="phpgacl/pager.tpl" pager_data=$paging_data link="?action=$action_escaped&filter_aco_section=$filter_aco_section_escaped&filter_aco=$filter_aco_escaped&filter_aro_section=$filter_aro_section_escaped&filter_aro=$filter_aro_escaped&filter_axo_section=$filter_axo_section_escaped&filter_axo=$filter_axo_escaped&filter_aro_group=$filter_aro_group_escaped&filter_axo_group=$filter_axo_group_escaped&filter_return_value=$filter_return_value_escaped&filter_allow=$filter_allow_escaped&filter_enabled=$filter_enabled_escaped&"}
	</td>
  </tr>
  <tr class="controls">
    <td colspan="6">&nbsp;</td>
    <td colspan="2" align="center">
      <input type="submit" class="button" name="action" value="Delete">
    </td>
  </tr>
</table>
<input type="hidden" name="return_page" value="{$return_page|attr}">
</form>
{include file="phpgacl/footer.tpl"}
