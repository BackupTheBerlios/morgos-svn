<?php
	// create the database
	// TODO: add prefix config
	include_once ('core/uimanager.class.php');
	$UI = new UIManager ();
	// include_once ('core/database.class.php');

	$SQL = file_get_contents ('install/sql/news.sql');
	$SQL .= file_get_contents ('install/sql/pages.sql');
	$SQL .= file_get_contents ('install/sql/users.sql');
	$SQL = ereg_replace ('%prefix%', 'morgos_', $SQL);

	$DB = $UI->getGenericDB ();
	$arrayOfSQL = explode (';', $SQL);
	foreach ($arrayOfSQL as $query) {
		$query = trim ($query);
		if (empty ($query)) {
			continue;
		}
		$result = $DB->query ($query);
		if ($result === false) {
			echo $query;
		}
	}

	$UI->addModule ('index', false, false, 1);
	$UI->addModule ('register', false, false, 0);
	
	$UI->addModule ('usersettings', true, false, 1, false);
	$UI->addModule ('logout', true, false , 3, false);
	
	$UI->addModule ('viewadmin', false, true, 2, false);
	$UI->addModule ('admin/database', false, true, 0, false);
	$UI->addModule ('admin/users', false, true, 0, false);
	$UI->addModule ('admin/news', false, true, 0, false);
	$UI->addModule ('admin/general', false, true, 0, false);
	$UI->addModule ('admin/addpage', false, true, 0, false);
	$UI->addModule ('admin/index', false, true, 0, false);
	$UI->addModule ('admin/pages', false, true, 0, false);
	$i10nMan = &$UI->i10nMan;
	$languages = $i10nMan->getAllSupportedLanguages ();
	foreach ($languages as $language) {
		$i10nMan->loadLanguage ($language);
		$UI->addPage ('index', $language, $i10nMan->translate ('Home'), $i10nMan->translate ('This is the homepage.'));
		$UI->addPage ('viewadmin', $language, $i10nMan->translate ('View admin'), '');
		$UI->addPage ('logout', $language, $i10nMan->translate ('Logout'), '');
		$UI->addPage ('register', $language, $i10nMan->translate ('Register'), '');
		$UI->addPage ('usersettings', $language, $i10nMan->translate ('Change your settings'), '');
	}

	$username = addslashes ($_POST['admin-account']);
	$email = addslashes ($_POST['admin-email']);
	$password = addslashes ($_POST['admin-password']);
	$UI->user->insertUser ($username, $email, $password, true);
?>
<?php echo '<?xml version="1.0"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $i10nMan->translate ('MorgOS Installation Wizard Step 5'); ?></title>
	</head>
	<body>
		<h1><?php echo $i10nMan->translate ('Install wizard MorgOS Step 5: Installation of database'); ?></h1>
		<form action='./index.php' method='post'>
			<div>
				<?php echo $i10nMan->translate ('Installation is done.'); ?>
				<?php echo $i10nMan->translate ('Remove the dir "install/" and file "install.php" and press "next" to go to the site.'); ?>
			</div>
			<input type='submit' value='<?php echo $i10nMan->translate ('Next'); ?>' />
		</form>
	</body>
</html>
