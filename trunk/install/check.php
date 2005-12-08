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
<html>
	<body>
		<div>
			<p>
			PHP Version 4.0.1 or higher: 
			<?php
				if (versionCompare (PHP_VERSION, '4.0.1', '>=')) {
					echo '<span class="ok">Yes</span>';
				} else {
					echo '<span class="notok">No</span>';
					$canrun = false;
				}
			?>
			</p>
			<p>Databases available: <br />
			<?php
				$DBMan = new genericDatabase ();
				$supported = $DBMan->getAllSupportedDatabases ();
				$db = false;
				foreach ($supported as $key => $support) {
					$db = true;
					echo $key . '<br />';
				}
				if ($db == false) {
					echo '<span class="notok">No database detected, please install a database plugin.</span>';
					$canrun = false;
				}
			?>
			</p>
		</div>
		<div>
			<?php
				if ($canrun == true) { ?>
					<form action='./install.php?phase=config' method='post'>
						<input type='hidden' name='canrun' value='yes' value='Next' />
						<input type='submit' value='Next' />
					</form>
			<?php
				} else {
					echo 'You can not install MorgOS, please check the requirements and try again.';
				}
			?>
			<form action='./install.php?phase=check' method='post'>
				<input type='hidden' value='yes' name='agree'>
				<input type='submit' value='Check again' />
			</form>
		</div>
	</body>
</html>
