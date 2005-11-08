<?php
	if (array_key_exists ('canrun', $_POST)) {
		if ($_POST['canrun'] == 'no') {
			header ('Location: ./install.php?phase=check');
		}
	} else {
		header ('Location: ./install.php?phase=check');
	}
?>
<html>
	<body>
		<form action='./install.php?phase=install' method='post'>
			<div>
				<h2>General options</h2>
				Site name: <input type="text" name="site-name"/> <br />
				<h2>Database options</h2>
				Database type: 
					<select name="database-type">
						<?php
							include_once ('core/compatible.php');
							include_once ('core/database.class.php');
							$DBMan = new genericDatabase ();
							$types = $DBMan->getAllSupportedDatabases ();
							foreach ($types as $key => $type) {
								echo '<option value="'.$key.'" />' . $key;
							}
						?>
					</select> <br />
				Database host: <input type="text" name="database-host"/> <br />
				Database name: <input type="text" name="database-name"/> <br />
				Database user: <input type="text" name="database-user"/> <br />
				Database password: <input type="password" name="database-password"/> <br />
				<input type='submit' value='Check databaseconnection' />
				<h2>Admin options</h2>
				The name of the admin account: <input type="text" name="admin-account"/> <br />
				The e-mail of the admin account: <input type="text" name="admin-email"/> <br />
				The password of the admin-account: <input type="password" name="admin-password"/> <br />
				The password of the admin-account: <input type="password" name="admin-password2"/> <br />
			</div>
			<input type='submit' value='Next' />
		</form>
	</body
</html>
