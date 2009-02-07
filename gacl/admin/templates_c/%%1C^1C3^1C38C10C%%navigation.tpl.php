<?php /* Smarty version 2.6.14, created on 2009-02-05 19:07:57
         compiled from phpgacl/navigation.tpl */ ?>
		<div id="top-tr"><div id="top-tl"><div id="top-br"><div id="top-bl">
			<h1><span>phpGACL</span></h1>
			<h2><?php echo $this->_tpl_vars['page_title']; ?>
</h2>
<?php if ($this->_tpl_vars['hidemenu'] != TRUE): ?>
			<ul id="menu">
				<li<?php if ($this->_tpl_vars['current'] == 'aro_group'): ?> class="current"<?php endif; ?>><a href="group_admin.php?group_type=aro">ARO Group Admin</a></li>
				<li<?php if ($this->_tpl_vars['current'] == 'axo_group'): ?> class="current"<?php endif; ?>><a href="group_admin.php?group_type=axo">AXO Group Admin</a></li>
				<li<?php if ($this->_tpl_vars['current'] == 'acl_admin'): ?> class="current"<?php endif; ?>><a href="acl_admin.php?return_page=acl_admin.php">ACL Admin</a></li>
				<li<?php if ($this->_tpl_vars['current'] == 'acl_list'): ?> class="current"<?php endif; ?>><a href="acl_list.php?return_page=acl_list.php">ACL List</a></li>
				<li<?php if ($this->_tpl_vars['current'] == 'acl_test'): ?> class="current"<?php endif; ?>><a href="acl_test.php">ACL Test</a></li>
				<li<?php if ($this->_tpl_vars['current'] == 'acl_debug'): ?> class="current"<?php endif; ?>><a href="acl_debug.php">ACL Debug</a></li>
				<li<?php if ($this->_tpl_vars['current'] == 'about'): ?> class="current"<?php endif; ?>><a href="about.php">About</a></li>
				<li><a href="../docs/manual.html" target="_blank">Manual</a></li>
				<li><a href="../docs/phpdoc/" >API Guide</a></li>
			</ul>
<?php endif; ?>
		</div></div></div></div>

		<div id="mid-r"><div id="mid-l">