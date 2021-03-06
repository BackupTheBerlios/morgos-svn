<?php
	include_once ('core/compatible.php');
	define ('NEWLINE', "\n"); // TODO make this work also for WIndows and Mac endlines
	// write the config file out
	$output = "<?php \n";
	$output .= "	/* This files is genereted by MorgOS, only change manual if you know what you are doing. */\n";
	$output .= '	$config[\'/general/sitename\'] = \'' . $_POST['site-name'] ."';" . NEWLINE;
	$output .= '	$config[\'/general/debug\'] = false;' . NEWLINE;
	$output .= '	$config[\'/database/type\'] = \'' . $_POST['database-type'] ."';" . NEWLINE;
	$output .= '	$config[\'/database/name\'] = \'' . $_POST['database-name'] ."';" . NEWLINE;
	$output .= '	$config[\'/database/host\'] = \'' . $_POST['database-host'] ."';" . NEWLINE;
	$output .= '	$config[\'/database/user\'] = \'' . $_POST['database-user'] ."';" . NEWLINE ;
	$output .= '	$config[\'/database/password\'] = \'' . $_POST['database-password'] ."';" . NEWLINE;
	$output .= '	$config[\'/extensions/WHATEVER\'] = false;' . NEWLINE;
	$output .= "?>";
	$fHandler = @fopen ('site.config.php', 'w');
	if ($fHandler !== false) {
		fwrite ($fHandler, $output);
		fclose ($fHandler);
	}

	$SQL = "CREATE DATABASE IF NOT EXISTS " . addslashes ($_POST['database-name']);
	$result = $DB->query ($SQL);
?>
<?php echo '<?xml version="1.0"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $i10nMan->translate ('MorgOS Installation Wizard Step 4') ?></title>
	</head>
	<body>
		<h1><?php echo $i10nMan->translate ('Install wizard MorgOS Step 4: Installation'); ?></h1>
		<form action='./install.php?phase=installdb' method='post'>
			<div>
				<?php
				if ($fHandler === false) {
					echo '<h2>' . $i10nMan->translate ('Save the folowing text in the file "site.config.php" in the directory where MorgOS is installed, then continue.') . '</h2>';
					$output =  htmlentities ($output);
					$output = nl2br ($output);
					echo $output;
					echo '<h2>' . $i10nMan->translate ('End of the content of site.config.php') . '</h2>';
				}
				?>
				<?php echo $i10nMan->translate ('Configuration is done, press "next" to create the database.'); ?>
			</div>
			<?php
				$username = addslashes ($_POST['admin-account']);
				$email = addslashes ($_POST['admin-email']);
				$password = addslashes ($_POST['admin-password']);
			?>
			<input type='hidden' name='admin-account' value='<?php echo $username ?>' />
			<input type='hidden' name='admin-email' value='<?php echo $email ?>' />
			<input type='hidden' name='admin-password' value='<?php echo $password ?>' />
			<input type='submit' value='<?php echo $i10nMan->translate ('Next'); ?>' />
		</form>
	</body>
</html>
