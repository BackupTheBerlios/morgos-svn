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
	}

	$UI->addModule ('index', false, false);
	$UI->addModule ('viewadmin', false, true);
	$UI->addModule ('admin/database', false, true, true);
	$UI->addModule ('admin/users', false, true, true);
	$UI->addModule ('admin/news', false, true, true);
	$UI->addModule ('admin/general', false, true, true);
	$UI->addModule ('admin/addpage', false, true, true);
	$UI->addModule ('admin/index', false, true, true);
	$UI->addModule ('admin/pages', false, true, true);
	$i10nMan = &$UI->i10nMan;
	$languages = $i10nMan->getAllSupportedLanguages ();
	foreach ($languages as $language) {
		$i10nMan->loadLanguage ($language);
		$UI->addPage ('index', $language, $i10nMan->translate ('Home'), $i10nMan->translate ('This is the homepage.'));
		$UI->addPage ('viewadmin', $language, $i10nMan->translate ('View admin'), '');
	}

	$username = addslashes ($_POST['admin-account']);
	$email = addslashes ($_POST['admin-email']);
	$password = addslashes ($_POST['admin-password']);
	$UI->user->insertUser ($username, $email, $password, true);
?>
<html>
	<body>
		<form action='./index.php' method='post'>
			<div>
				Installation is done, press next to continue.
			</div>
			<input type='submit' value='Next' />
		</form>
	</body>
</html>
