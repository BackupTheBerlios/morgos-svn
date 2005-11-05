<html>
	<body>
		<form action='./install.php?phase=done' method='post'>
			<div>
				<?php
					include_once ('core/compatible.php');
					define ('NEWLINE', "\n"); // TODO make this work also for WIndows and Mac endlines
				
					// write the config file out
					$output = "<?php \n";
					$output .= "	/* This files is genereted by MorgOS, only change manual if you know what you are doing. */\n";
					$output .= '	$config[\'/general/sitename\'] = \'' . $_POST['site-name'] ."';" . NEWLINE;
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
					} else {
						echo '<h2>Save the folowing text in the file "site.config.php" in the directory where MorgOS is installed, then continue.</h2>';
						$output =  htmlentities ($output);
						$output = nl2br ($output);
						echo $output;
						echo '<h2>End of the content of site.config.php</h2>';
					}
					
					// create the database
					// TODO: add prefix config
					include_once ('core/database.class.php');
					$DBMan = new genericDatabase ();
					$DB = $DBMan->load ($_POST['database-type']);
					$DB->connect ($_POST['database-host'], $_POST['database-user'], $_POST['database-password']);
					
					$SQL = "CREATE DATABASE IF NOT EXISTS " . $_POST['database-name'];
					$DB->query ($SQL);
					$DB->select_db ($_POST['database-name']);
					
					$SQL = file_get_contents ('install/sql/news.sql');
					$SQL .= file_get_contents ('install/sql/pages.sql');
					$SQL = ereg_replace ('%prefix%', 'morgos_', $SQL);
					
					$arrayOfSQL = explode (';', $SQL);
					foreach ($arrayOfSQL as $query) {
						$query = trim ($query);
						if (empty ($query)) {
							continue;
						}
						$result = $DB->query ($query);
						if ($result !== false) {
							echo '<p>' .$query . '</p>';
						} else {
							echo '<p class="error">' .$query . '</p>';
						}
					}
					
					include_once ('core/uimanager.class.php');
					$UI = new UIManager ();
					$UI->addModule ('index.html', false);
					$i10nMan = &$UI->i10nMan;
					$languages = $i10nMan->getAllSupportedLanguages ();
					foreach ($languages as $language) {
						$i10nMan->loadLanguage ($language);
						$UI->addPage ('index.html', $language, $i10nMan->translate ('Home'), $i10nMan->translate ('This is the homepage.'));
					}
					// create the admin user TODO
				?>
			</div>
			<input type='submit' value='Next' />
		</form>
	</body>
</html>
