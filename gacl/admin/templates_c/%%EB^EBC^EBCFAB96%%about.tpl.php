<?php /* Smarty version 2.6.14, created on 2009-02-06 03:21:02
         compiled from phpgacl/about.tpl */ ?>
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
    <div style="text-align: center;">
      <table cellpadding="2" cellspacing="2" border="2" align="center">
        <tbody>
		<?php if ($this->_tpl_vars['first_run'] != 1): ?>
          <tr>
			<th>
				Help
			</th>
          </tr>
          <tr>
			<td>
				Please join the <a href="https://sourceforge.net/mail/?group_id=57103">Mailing Lists</a> if you have
				any questions, comments, or support questions.
				<p><b>TIP</b>: Searching the Mailing List archives may be a good idea prior to emailing the list, <br>
				as well the below "Report" information may be helpful in any support questions.</p>
				<p>PLEASE DO NOT EMAIL ME DIRECTLY REGARDING SUPPORT QUESTIONS<br />
				You will receive answers faster on the mailing list, and any answers given may benefit others.</p>
				But if you must email me (Mike Benoit) directly, click <a href="mailto:ipso@snappymail.ca">here</a>.
			</td>
          </tr>
          <tr>
			<th>
				Donate
			</th>
          </tr>
          <tr>
			<td>
				Time working on phpGACL means less time that I can work to get paid.<br />
				Therefore any donations I receive will help me to devote more time to developing phpGACL.
				<p>However, I'd much rather donations in the form of code and/or documentation.</p>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" align="center">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="ipso@snappymail.ca">
				<input type="hidden" name="item_name" value="php Generic Access Control List">
				<input type="hidden" name="no_note" value="1">
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="tax" value="0">
				<input type="image" class="paypal" src="https://www.paypal.com/images/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
				</form>
			</td>
          </tr>
        <?php endif; ?>
          <tr>
			<th>
				<?php if ($this->_tpl_vars['first_run'] != 1): ?>
					Report
				<?php else: ?>
					<font color="#ff0000">* Report *</font>
				<?php endif; ?>
			</th>
          </tr>
          <tr>
			<td>
				Report some basic information back to the phpGACL project so we know where to spend our time.<br />
				<b>All information will be kept private, will not be sold, and will only be used for informational purposes regarding phpGACL.</b>
				<br /><br />
    		<form method="post" name="about" action="about.php" align="center">
				<textarea name="system_information" rows="10" cols="60" wrap="VIRTUAL"><?php echo $this->_tpl_vars['system_info']; ?>
</textarea>
				<br />
				<input type="hidden" name="system_info_md5" value="<?php echo $this->_tpl_vars['system_info_md5']; ?>
" />
				<input type="submit" name="action" value="Submit" />
				</form>
			</td>
          </tr>
		<?php if ($this->_tpl_vars['first_run'] != 1): ?>
		  <tr>
			<th>
				Credits
			</th>
          </tr>
          <tr>
			<td>
<pre>
<?php echo $this->_tpl_vars['credits']; ?>

</pre>
			</td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>