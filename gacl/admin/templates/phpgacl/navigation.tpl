		<div id="top-tr"><div id="top-tl"><div id="top-br"><div id="top-bl">
			<h1><span>phpGACL</span></h1>
			<h2>{$page_title|escape:'html'}</h2>
			<p><a href='../../interface/usergroup/adminacl.php' onclick='top.restoreSession()'><span style='font-size: 80%;'>(Back to OpenEMR's ACL menu)</span></a></p>
{if $hidemenu neq TRUE}
			<ul id="menu">
				<li{if $current eq 'aro_group'} class="current"{/if}><a href="group_admin.php?group_type=aro">ARO Group Admin</a></li>
				<li{if $current eq 'axo_group'} class="current"{/if}><a href="group_admin.php?group_type=axo">AXO Group Admin</a></li>
				<li{if $current eq 'acl_admin'} class="current"{/if}><a href="acl_admin.php?return_page=acl_admin.php">ACL Admin</a></li>
				<li{if $current eq 'acl_list'} class="current"{/if}><a href="acl_list.php?return_page=acl_list.php">ACL List</a></li>
				<li{if $current eq 'acl_test'} class="current"{/if}><a href="acl_test.php">ACL Test</a></li>
				<li{if $current eq 'acl_debug'} class="current"{/if}><a href="acl_debug.php">ACL Debug</a></li>
				<li{if $current eq 'about'} class="current"{/if}><a href="about.php">About</a></li>
				<li><a href="../docs/manual.html" target="_blank">Manual</a></li>
				<li><a href="../docs/phpdoc/" >API Guide</a></li>
			</ul>
{/if}
		</div></div></div></div>

		<div id="mid-r"><div id="mid-l">
