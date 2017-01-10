<?php
	$this->assign('title','Patient Portal Secure');
	$this->assign('nav','secureapp');

	$this->display('_Header.tpl.php');
?>

<div class="container">

	<?php if ($this->feedback) { ?>
		<div class="alert alert-error">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<?php $this->eprint($this->feedback); ?>
		</div>
	<?php } ?>

	<!-- #### this view/tempalate is used for multiple pages.  the controller sets the 'page' variable to display differnet content ####  -->

	<?php if ($this->page == 'login') { ?>

		<div class="hero-unit">
			<h1>Login</h1>
			<p>This portals authentication.  <strong>Your credentials are provided by your provider</strong>.</p>
			<p>
				<a href="secureuser" class="btn btn-primary btn-large">Patient Access</a>
				<a href="secureadmin" class="btn btn-primary btn-large">Provider Access</a>
				<?php if (isset($this->currentUser)) { ?>
					<a href="logout" class="btn btn-primary btn-large">Logout</a>
				<?php } ?>
			</p>
		</div>

		<form class="well" method="post" action="login">
			<fieldset>
			<legend>Enter your credentials</legend>
				<div class="control-group">
				<input id="username" name="username" type="text" placeholder="Username..." />
				</div>
				<div class="control-group">
				<input id="password" name="password" type="password" placeholder="Password..." />
				</div>
				<div class="control-group">
				<button type="submit" class="btn btn-primary">Login</button>
				</div>
			</fieldset>
		</form>

	<?php } else { ?>

		<div class="hero-unit">
			<h1>Secure <?php $this->eprint($this->page == 'userpage' ? 'Patient' : 'Provider'); ?> Page</h1>
			<p>This page is accessible only to <?php $this->eprint($this->page == 'userpage' ? 'authenticated patients' : 'administrators'); ?>.
			You are currently logged in as '<strong><?php $this->eprint($this->currentUser->Username); ?></strong>'</p>
			<p>
				<a href="secureuser" class="btn btn-primary btn-large">Visit Patient Home Page</a>
				<a href="secureadmin" class="btn btn-primary btn-large">Visit Provider Home Page</a>
				<a href="logout" class="btn btn-primary btn-large">Logout</a>
			</p>
		</div>
	<?php } ?>

</div> <!-- /container -->

<?php
	$this->display('_Footer.tpl.php');
?>