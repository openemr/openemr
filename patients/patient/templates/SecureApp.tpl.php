<?php
/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */

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
			<h1><?php echo xlt('Login'); ?></h1>
			<p><?php echo xlt('This portals authentication.'); ?> <strong><?php echo xlt('Your credentials are provided by your provider'); ?></strong>.</p>
			<p>
				<a href="secureuser" class="btn btn-primary btn-large"><?php echo xlt('Patient Access'); ?></a>
				<a href="secureadmin" class="btn btn-primary btn-large"><?php echo xlt('Provider Access'); ?></a>
				<?php if (isset($this->currentUser)) { ?>
					<a href="logout" class="btn btn-primary btn-large"><?php echo xlt('Logout'); ?></a>
				<?php } ?>
			</p>
		</div>

		<form class="well" method="post" action="login">
			<fieldset>
			<legend><?php echo xlt('Enter your credentials'); ?></legend>
				<div class="control-group">
				<input id="username" name="username" type="text" placeholder="<?php echo xlt('Username'); ?>..." />
				</div>
				<div class="control-group">
				<input id="password" name="password" type="password" placeholder="<?php echo xlt('Password'); ?>..." />
				</div>
				<div class="control-group">
				<button type="submit" class="btn btn-primary"><?php echo xlt('Login'); ?></button>
				</div>
			</fieldset>
		</form>

	<?php } else { ?>

		<div class="hero-unit">
			<h1>Secure <?php $this->eprint($this->page == 'userpage' ? 'Patient' : 'Provider'); ?> Page</h1>
			<p>This page is accessible only to <?php $this->eprint($this->page == 'userpage' ? 'authenticated patients' : 'administrators'); ?>.
			<?php echo xlt('You are currently logged in as'); ?> '<strong><?php $this->eprint($this->currentUser->Username); ?></strong>'</p>
			<p>
				<a href="secureuser" class="btn btn-primary btn-large"><?php echo xlt('Visit Patient Home Page'); ?></a>
				<a href="secureadmin" class="btn btn-primary btn-large"><?php echo xlt('Visit Provider Home Page'); ?></a>
				<a href="logout" class="btn btn-primary btn-large"><?php echo xlt('Logout'); ?></a>
			</p>
		</div>
	<?php } ?>

</div> <!-- /container -->

<?php
	$this->display('_Footer.tpl.php');
?>