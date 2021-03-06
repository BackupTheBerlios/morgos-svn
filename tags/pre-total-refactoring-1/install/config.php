<?php echo '<?xml version="1.0"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $i10nMan->translate ('MorgOS Installation Wizard Step 3'); ?></title>
	</head>
	<body>
		<h1><?php echo $i10nMan->translate ('Install wizard MorgOS Step 3: Configuration'); ?></h1>
		<?php showAllErrors (); ?>
		<form action='./install.php?phase=install' method='post'>
			<div>
				<h2><?php echo $i10nMan->translate ('General options'); ?></h2>
				<?php echo $i10nMan->translate ('Site name'); ?>: <input type="text" name="site-name" value="<?php echo getParam ('site-name')?>"/> <br />
				<h2><?php echo $i10nMan->translate ('Database options'); ?> </h2>
				<?php echo $i10nMan->translate ('Database type'); ?>: 
					<select name="database-type">
						<?php
							include_once ('core/compatible.php');
							include_once ('core/database.class.php');
							$DBMan = new genericDatabase ();
							$types = $DBMan->getAllSupportedDatabases ();
							foreach ($types as $key => $type) {
								if ($key == getParam ('database-type')) {
									//echo $key;
									echo '<option value="'.$key.'" selected="selected">' . $key . '</option>';
								} else {
									echo '<option value="'.$key.'">' . $key . '</option>';
								}
							}
						?>
					</select> <br />
				<?php echo $i10nMan->translate ('Database host'); ?>: <input type="text" name="database-host" value="<?php echo getParam ('database-host')?>"/> <br />
				<?php echo $i10nMan->translate ('Database name'); ?>: <input type="text" name="database-name" value="<?php echo getParam ('database-name')?>"/> <br />
				<?php echo $i10nMan->translate ('Database user'); ?>: <input type="text" name="database-user" value="<?php echo getParam ('database-user')?>"/> <br />
				<?php echo $i10nMan->translate ('Database password') ?>: <input type="password" name="database-password"/> <br />
				<!--<input type='submit' value='Check databaseconnection' />-->
				<h2><?php echo $i10nMan->translate ('Admin options'); ?></h2>
				<?php echo $i10nMan->translate ('The name of the admin account'); ?>: <input type="text" name="admin-account" value="<?php echo getParam ('admin-account')?>"/> <br />
				<?php echo $i10nMan->translate ('The e-mail of the admin account'); ?>: <input type="text" name="admin-email" value="<?php echo getParam ('admin-email')?>"/> <br />
				<?php echo $i10nMan->translate ('The password of the admin-account') ?>: <input type="password" name="admin-password"/> <br />
				<?php echo $i10nMan->translate ('The password of the admin-account (repeat)') ?>: <input type="password" name="admin-password2"/> <br />
			</div>
			<input type="hidden" name="canrun" value="yes" />
			<input type='submit' value='<?php echo $i10nMan->translate ('Next') ?>' />
		</form>
	</body>
</html>
