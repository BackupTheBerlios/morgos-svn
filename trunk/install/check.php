<?php
	include_once ('core/compatible.php');
	include_once ('core/database.class.php');
	$canrun = true;
	if (array_key_exists ('agree', $_POST)) {
		if ($_POST['agree'] == 'no') {
			header ('Location: ./install.php');
		}
	} else {
		header ('Location: ./install.php');
	}
?>
<?php echo '<?xml version="1.0"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $i10nMan->translate ('MorgOS Installation Wizard Step 2'); ?></title>
	</head>
	<body>
		<div>
			<h1><?php echo $i10nMan->translate ('MorgOS Installation Wizard Step 2: Requirements check'); ?></h1>
			<h2><?php echo $i10nMan->translate ('Required'); ?></h2>
			<p><?php echo $i10nMan->translate ('PHP Version 4.0.0 or higher'); ?>: 
			<?php
				if (versionCompare (PHP_VERSION, '4.0.0', '>=')) {
					echo '<span class="ok">' . $i10nMan->translate ('Yes') . '</span>';
				} else {
					echo '<span class="notok">' . $i10nMan->translate ('No') . '</span>';
					$canrun = false;
				}
				if (versionCompare (PHP_VERSION, '4.1.0', '<') and (versionCompare (PHP_VERSION, '4.0.0', '>='))) {
					echo ' <span class="warning"> ' . $i10nMan->translate ('MorgOS is untested on PHP lower than PHP 4.1.0, if you encouter problems please report this. '). '</span>';
				}
			?>
			</p>
			<p><?php echo $i10nMan->translate ('Databases available'); ?></p>
			<?php
				$DBMan = new genericDatabase ();
				$supported = $DBMan->getAllSupportedDatabases ();
				$db = false;
				echo '<ul>';
				foreach ($supported as $key => $support) {
					$db = true;
					echo '<li>' . $key . '</li>';
				}
				echo '</ul>';
				if ($db == false) {
					echo '<span class="notok">' . $i10nMan->translate ('No database detected, please install a database plugin.') . '</span>';
					$canrun = false;
				}
			?>
			<h2><?php echo $i10nMan->translate ('Optional'); ?></h2>
			<?php echo $i10nMan->translate ('This space left intentionnally blank.'); ?>
		</div>
		<div>
			<?php
				if ($canrun == true) { ?>
					<form action='./install.php?phase=config' method='post'>
						<input type='hidden' name='canrun' value='yes' />
						<input type='submit' value='<?php echo $i10nMan->translate ('Next'); ?>' />
					</form>
			<?php
				} else {
					echo $i10nMan->translate ('You can not install MorgOS, please check the requirements and try again.');
				}
			?>
			<form action='./install.php?phase=check' method='post'>
				<input type='hidden' value='yes' name='agree' />
				<input type='submit' value='<?php echo $i10nMan->translate ('Check again.'); ?>' />
			</form>
		</div>
	</body>
</html>
