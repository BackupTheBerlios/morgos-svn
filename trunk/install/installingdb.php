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

	$UI->addModule ('index', false);
	$i10nMan = &$UI->i10nMan;
	$languages = $i10nMan->getAllSupportedLanguages ();
	foreach ($languages as $language) {
		$i10nMan->loadLanguage ($language);
		$UI->addPage ('index', $language, $i10nMan->translate ('Home'), $i10nMan->translate ('This is the homepage.'));
	}

	$UI->user->insertUser ($_POST['admin-account'], $_POST['admin-email'], $_POST['admin-password'], true);
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
