<?php
	/*if ($_POST['admin-password'] != $_POST['admin-password2']) {
		header ('Location: install.php'); // this is not admin-friendly FIXME 
	}

	include_once ('core/compatible.php');
	define ('NEWLINE', "\n"); // TODO make this work also for WIndows and Mac endlines
	// write the config file out
	$output = "<?php \n";*/
//	$output .= "	/* This files is genereted by MorgOS, only change manual if you know what you are doing. */\n";
/*	$output .= '	$config[\'/general/sitename\'] = \'' . $_POST['site-name'] ."';" . NEWLINE;
	$output .= '	$config[\'/database/type\'] = \'' . $_POST['database-type'] ."';" . NEWLINE;
	$output .= '	$config[\'/database/name\'] = \'' . $_POST['database-name'] ."';" . NEWLINE;
	$output .= '	$config[\'/database/host\'] = \'' . $_POST['database-host'] ."';" . NEWLINE;
	$output .= '	$config[\'/database/user\'] = \'' . $_POST['database-user'] ."';" . NEWLINE ;
	$output .= '	$config[\'/database/password\'] = \'' . $_POST['database-password'] ."';" . NEWLINE;
	$output .= "?>";
	$fHandler = @fopen ('site.config.php', 'w');
	if ($fHandler !== false) {
		fwrite ($fHandler, $output);
		fclose ($fHandler);
	}
	include_once ('core/database.class.php');
	$DBMan = new genericDatabase ();
	$DB = $DBMan->load ($_POST['database-type']);
	$DB->connect ($_POST['database-host'], $_POST['database-user'], $_POST['database-password']);

	$SQL = "CREATE DATABASE IF NOT EXISTS " . $_POST['database-name'];
	$DB->query ($SQL);*/
?>
<?php echo '<?xml version="1.0"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>MorgOS Installation Wizard Step 4</title>
	</head>
	<body>
		<h1>Install wizard MorgOS Step 4: Installation</h1>
		<form action='./install.php?phase=installdb' method='post'>
			<div>
				<?php
				if ($fHandler === false) {
					echo '<h2>Save the folowing text in the file "site.config.php" in the directory where MorgOS is installed, then continue.</h2>';
					$output =  htmlentities ($output);
					$output = nl2br ($output);
					echo $output;
					echo '<h2>End of the content of site.config.php</h2>';
				}
				?>
				Configuration is done, press "next" to create the database.
			</div>
			<?php
				$username = addslashes ($_POST['admin-account']);
				$email = addslashes ($_POST['admin-email']);
				$password = addslashes ($_POST['admin-password']);
			?>
			<input type='hidden' name='admin-account' value='<?php echo $username ?>' />
			<input type='hidden' name='admin-email' value='<?php echo $email ?>' />
			<input type='hidden' name='admin-password' value='<?php echo $password ?>' />
			<input type='submit' value='Next' />
		</form>
	</body>
</html>
