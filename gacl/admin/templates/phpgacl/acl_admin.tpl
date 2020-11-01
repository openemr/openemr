{include file="phpgacl/header.tpl"}
{if isset($js_array)}
    <script>{$js_array}</script>
{/if}
{include file="phpgacl/acl_admin_js.tpl"}
  </head>
<body onload="populate(document.acl_admin.aco_section,document.acl_admin.elements['aco[]'], '{if isset($js_aco_array_name)}{$js_aco_array_name}{/if}');populate(document.acl_admin.aro_section,document.acl_admin.elements['aro[]'], '{if isset($js_aro_array_name)}{$js_aro_array_name}{/if}')">
{include file="phpgacl/navigation.tpl"}
  <form method="post" name="acl_admin" action="acl_admin.php" onsubmit="select_all(document.acl_admin.elements['selected_aco[]']);select_all(document.acl_admin.elements['selected_aro[]']);select_all(document.acl_admin.elements['selected_aro[]']);return true;">
    <input type="hidden" name="csrf_token_form" value="{$CSRF_TOKEN_FORM|attr}">
    <div align="center">
      <table cellpadding="2" cellspacing="2" border="2" align="center">
        <tbody>
          <tr>
            <th width="24%">Sections</th>
            <th width="24%">Access Control Objects</th>
            <th width="4%">&nbsp;</th>
            <th width="24%">Selected</th>
            <th width="24%">Access</th>
          </tr>
          <tr valign="top" align="center">
            <td>
              [ <a href="edit_object_sections.php?object_type=aco&return_page={if isset($return_page)}{$return_page|attr_url}{/if}">Edit</a> ]
              <br />
              <select name="aco_section" tabindex="0" size="10" onclick="populate(document.acl_admin.aco_section,document.acl_admin.elements['aco[]'], '{if isset($js_aco_array_name)}{$js_aco_array_name}{/if}')">
                  {if isset($options_aco_sections)}
                      {if isset($aco_section_value)}
                          {html_options options=$options_aco_sections selected=$aco_section_value}
                      {else}
                          {html_options options=$options_aco_sections}
                      {/if}
                  {/if}
              </select>
            </td>
            <td>
              [ <a href="javascript: location.href = 'edit_objects.php?object_type=aco&section_value=' + document.acl_admin.aco_section.options[document.acl_admin.aco_section.selectedIndex].value + '&return_page={if isset($return_page)}{$return_page|attr_url}{/if}';">Edit</a> ]
              <br />
              <select name="aco[]" tabindex="0" size="10" width="200" multiple>
              </select>
            </td>
            <td valign="middle">
              <br /><input type="button" class="select" name="select" value="&nbsp;&gt;&gt;&nbsp;" onClick="select_item(document.acl_admin.aco_section, document.acl_admin.elements['aco[]'], document.acl_admin.elements['selected_aco[]'])">
              <br /><input type="button" class="deselect" name="deselect" value="&nbsp;&lt;&lt;&nbsp;" onClick="deselect_item(document.acl_admin.elements['selected_aco[]'])">
            </td>
            <td>
              <br />
              <select name="selected_aco[]" tabindex="0" size="10" multiple>
                {if isset($options_selected_aco)}
				  {html_options options=$options_selected_aco selected=$selected_aco}
                {/if}
              </select>
            </td>
            <td valign="middle">
              <table class="invisible">
                <tr align="left"><td><input type="radio" class="radio" name="allow" value="1" {if isset($allow) && $allow==1}checked{/if} /></td><td>Allow</td></tr>
                <tr align="left"><td><input type="radio" class="radio" name="allow" value="0" {if isset($allow) && $allow==0}checked{/if} /></td><td>Deny</td></tr>
              	<tr class="spacer"><td colspan="2"></td></tr>
              	<tr align="left"><td><input type="checkbox" class="checkbox" name="enabled" value="1" {if isset($enabled) && $enabled==1}checked{/if} /></td><td>Enabled</td></tr>
             </table>
           </td>
          </tr>

          <tr>
            <th>Sections</th>
            <th>Access Request Objects</th>
            <th>&nbsp;</th>
            <th>Selected</th>
            <th>Groups</th>
          </tr>
          <tr valign="top" align="center">
            <td>
              [ <a href="edit_object_sections.php?object_type=aro&return_page={if isset($return_page)}{$return_page|attr_url}{/if}">Edit</a> ]
              <br />
              <select name="aro_section" tabindex="0" size="10" onclick="populate(document.acl_admin.aro_section,document.acl_admin.elements['aro[]'],'{if isset($js_aro_array_name)}{$js_aro_array_name}{/if}')">
                  {if isset($options_aro_sections)}
                      {html_options options=$options_aro_sections selected=$aro_section_value}
                  {/if}
              </select>
            </td>
            <td>
              [ <a href="javascript: location.href = 'edit_objects.php?object_type=aro&section_value=' + document.acl_admin.aro_section.options[document.acl_admin.aro_section.selectedIndex].value + '&return_page={if isset($return_page)}{$return_page|attr_url}{/if}';">Edit</a> ]
              [ <a href="#" onClick="window.open('object_search.php?src_form=acl_admin&object_type=aro&section_value=' + document.acl_admin.aro_section.options[document.acl_admin.aro_section.selectedIndex].value + '&return_page={if isset($return_page)}{$return_page|attr}{/if}','','status=yes,width=400,height=400');return false;">Search</a> ]
              <br />
              <select name="aro[]" tabindex="0" size="10" width="200" multiple>
              </select>
            </td>
            <td valign="middle">
              <br /><input type="button" class="select" name="select" value="&nbsp;&gt;&gt;&nbsp;" onClick="select_item(document.acl_admin.aro_section, document.acl_admin.elements['aro[]'], document.acl_admin.elements['selected_aro[]'])">
              <br /><input type="button" class="deselect" name="deselect" value="&nbsp;&lt;&lt;&nbsp;" onClick="deselect_item(document.acl_admin.elements['selected_aro[]'])">
            </td>
            <td>
             <br />
             <select name="selected_aro[]" tabindex="0" size="10" multiple>
               {if isset($options_selected_aro)}
			       {html_options options=$options_selected_aro selected=$selected_aro}
               {/if}
             </select>
            </td>
            <td>
              [ <a href="group_admin.php?group_type=aro&return_page={$SCRIPT_NAME|attr_url}?action={$action|attr_url}&acl_id={if isset($acl_id)}{$acl_id|attr_url}{/if}">Edit</a> ]
              <br />
			  <select name="aro_groups[]" tabindex="0" size="8" multiple>
                  {if isset($options_aro_groups)}
			          {html_options options=$options_aro_groups selected=$selected_aro_groups}
                  {/if}
			  </select>
			  <br /><input type="button" class="un-select" name="Un-Select" value="Un-Select" onClick="unselect_all(document.acl_admin.elements['aro_groups[]'])">
            </td>
          </tr>

          <tr>
            <th colspan="5">
              [ <a href="javascript: showObject('axo_row1');showObject('axo_row2');setCookie('show_axo',1);">Show</a> / <a href="javascript: hideObject('axo_row1');hideObject('axo_row2');deleteCookie('show_axo');">Hide</a> ] Access eXtension Objects (Optional)
            </th>
          </tr>

          <tr id="axo_row1" {if !isset($show_axo) || !$show_axo}class="hide"{/if}>
            <th>Sections</th>
            <th>Access eXtension Objects</th>
            <th>&nbsp;</th>
            <th>Selected</th>
            <th>Groups</th>
          </tr>
          <tr valign="top" align="center" id="axo_row2" {if !isset($show_axo) || !$show_axo}class="hide"{/if}>
            <td>
              [ <a href="edit_object_sections.php?object_type=axo&return_page={if isset($return_page)}{$return_page|attr_url}{/if}">Edit</a> ]
              <br />
              <select name="axo_section" tabindex="0" size="10" onclick="populate(document.acl_admin.axo_section,document.acl_admin.elements['axo[]'],'{if isset($js_axo_array_name)}{$js_axo_array_name}{/if}')">
                  {if isset($options_axo_sections)}
                      {html_options options=$options_axo_sections selected=$axo_section_value}
                  {/if}
              </select>
            </td>
            <td>
              [ <a href="javascript: location.href = 'edit_objects.php?object_type=axo&section_value=' + document.acl_admin.axo_section.options[document.acl_admin.axo_section.selectedIndex].value + '&return_page={if isset($return_page)}{$return_page|attr_url}{/if}';">Edit</a> ]
              [ <a href="#" onClick="window.open('object_search.php?src_form=acl_admin&object_type=axo&section_value=' + document.acl_admin.axo_section.options[document.acl_admin.axo_section.selectedIndex].value + '&return_page={if isset($return_page)}{$return_page|attr}{/if}','','status=yes,width=400,height=400');return false;">Search</a> ]
              <br />
              <select name="axo[]" tabindex="0" size="10" width="200" multiple>
              </select>
            </td>
            <td valign="middle">
              <br /><input type="button" class="select" name="select" value="&nbsp;&gt;&gt;&nbsp;" onClick="select_item(document.acl_admin.axo_section, document.acl_admin.elements['axo[]'], document.acl_admin.elements['selected_axo[]'])">
              <br /><input type="button" class="deselect" name="deselect" value="&nbsp;&lt;&lt;&nbsp;" onClick="deselect_item(document.acl_admin.elements['selected_axo[]'])">
            </td>
            <td>
              <br />
              <select name="selected_axo[]" tabindex="0" size="10" multiple>
                {if isset($options_selected_axo)}
                    {html_options options=$options_selected_axo selected=$selected_axo}
                {/if}
              </select>
            </td>
            <td>
              [ <a href="group_admin.php?group_type=axo&return_page={$SCRIPT_NAME|attr_url}?action={$action|attr_url}&acl_id={if isset($acl_id)}{$acl_id|attr_url}{/if}">Edit</a> ]
              <br />
              <select name="axo_groups[]" tabindex="0" size="8" multiple>
                  {if isset($options_axo_groups)}
                      {html_options options=$options_axo_groups selected=$selected_axo_groups}
                  {/if}
              </select>
              <br /><input type="button" class="un-select" name="Un-Select" value="Un-Select" onClick="unselect_all(document.acl_admin.elements['axo_groups[]'])">
            </td>
        </tr>

        <tr>
			<th colspan="5">Miscellaneous Attributes</th>
		</tr>
        <tr valign="top" align="left">
			<td align="center">
                <b>ACL Section</b>
            </td>
			<td>
                <b>Extended Return Value:</b>
            </td>
            <td colspan="4">
                <input type="text" name="return_value" size="50" value="{if isset($return_value)}{$return_value|attr}{/if}" id="return_value">
            </td>
		</tr>
		<tr valign="top" align="left">
			<td align="center">
			[ <a href="edit_object_sections.php?object_type=acl&return_page={if isset($return_page)}{$return_page|attr_url}{/if}">Edit</a> ]
			<br />
			<select name="acl_section" tabindex="0" size="3">
                {if isset($options_acl_sections)}
                    {if isset($acl_section_value)}
			            {html_options options=$options_acl_sections selected=$acl_section_value}
                    {else}
                        {html_options options=$options_acl_sections}
                    {/if}
                {/if}
			</select>
		  </td>
          <td><b>Note:</b></td>
          <td colspan="4"><textarea name="note" rows="4" cols="40">{if isset($note)}{$note|text}{/if}</textarea></td>
		</tr>
        <tr class="controls" align="center">
          <td colspan="5">
            <input type="submit" class="button" name="action" value="Submit"> <input type="reset" class="button" value="Reset">
          </td>
        </tr>
      </tbody>
    </table>
	<input type="hidden" name="acl_id" value="{if isset($acl_id)}{$acl_id|attr}{/if}">
	<input type="hidden" name="return_page" value="{if isset($return_page)}{$return_page|attr}{/if}">
  </div>
</form>
{include file="phpgacl/footer.tpl"}
