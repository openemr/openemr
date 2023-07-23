{include file="phpgacl/header.tpl"}
    <style type="text/css">

    input.search {
    	width: 99%;
    }
    select.search {
    	margin-top: 0px;
    	width: 99%;
    }

    </style>
{include file="phpgacl/acl_admin_js.tpl"}
  </head>
  <body onload="document.object_search.name_search_str.focus();">
{include file="phpgacl/navigation.tpl" hidemenu="1"}
    <form method="get" name="object_search" action="object_search.php">
      <table cellpadding="2" cellspacing="2" border="2" width="100%">
        <tbody>
          <tr>
            <th colspan="2">{$object_type_name|text} > {$section_value_name|text}</th>
          </tr>
          <tr>
            <td width="25%"><b>Name:</b></td>
            <td width="75%"><input type="text" class="search" name="name_search_str" value="{$name_search_str|attr}" /></td>
          </tr>
          <tr>
			<td><b>Value:</b></td>
			<td><input type="text" class="search" name="value_search_str" value="{$value_search_str|attr}" /></td>
		  </tr>
		  <tr class="controls" align="center">
		  	<td colspan="2"><input type="submit" class="button" name="action" value="Search" /> <input type="button" class="button" name="action" value="Close" onClick="window.close();" /></td>
          </tr>
        </tbody>
      </table>
{if (strlen($total_rows) != 0)}
	  <br />
      <table cellpadding="2" cellspacing="2" border="2" width="100%">
        <tbody>
          <tr>
            <th colspan="2">{$total_rows|text} Objects Found</th>
          </tr>
		{if ($total_rows > 0)}
          <tr valign="middle" align="center">
            <td width="90%">
			  <select name="objects" class="search" tabindex="0" size="10" multiple>
			    {html_options options=$options_objects}
			  </select>
            </td>
            <td width="10%">
				<input type="button" class="select" name="select" value="&nbsp;&gt;&gt;&nbsp;" onClick="opener.select_item(opener.document.forms['{$src_form|attr}'].elements['{$object_type|attr}_section'], this.form.elements['objects'], opener.document.forms['{$src_form|attr}'].elements['selected_{$object_type|attr}[]']);">
             </td>
          </tr>
		{/if}
        </tbody>
      </table>
{/if}
	<input type="hidden" name="src_form" value="{$src_form|attr}">
	<input type="hidden" name="object_type" value="{$object_type|attr}">
	<input type="hidden" name="section_value" value="{$section_value|attr}">
  </form>
{include file="phpgacl/footer.tpl"}
