<?php
	$canrun = true;
	if ($_POST['agree'] == 'no') {
		header ('Location: ./install.php');
	}
?>
<html>
	<body>
		<div>
			<p>
			PHP Version 4.1 or higher: 
			<?php
				if (version_compare (PHP_VERSION,'4.1','>=')) {
					echo '<span class="ok">Yes</span>';
				} else {
					echo '<span class="notok">No</span>';
					$canrun = false;
				}
			?>
			</p>
			<p>Databases available: <br />
			<?php
				include ('core/database.class.php');
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
						<input type='submit' value='Next' />
					</form>
			<?php
				} else {
					echo 'You can not install MorgOS, please check the requirements and try again.';
				}
			?>
			<form action='./install.php?phase=check' method='post'>
				<input type='submit' value='Check again' />
			</form>
		</div>
	</body>
</html>
