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

	$UI->pages->addModule ('index'              , false, false, 1, 0, true);
	$UI->pages->addModule ('register'           , false, false, 0, 0, false);
	$UI->pages->addModule ('forgotpass'         , false, false, 0, 0, false);
	$UI->pages->addModule ('usersettings'       , true , false, 1, 0, false);
	$UI->pages->addModule ('logout'             , true , false, 3, 0, false);
	$UI->pages->addModule ('viewadmin'          , false, true , 2, 0, false);
	$UI->pages->addModule ('admin/addpage'      , false, true , 0, 0, true);
	$UI->pages->addModule ('admin/index'        , false, true , 0, 1, true);
	$UI->pages->addModule ('admin/general'      , false, true , 0, 2, true);
	$UI->pages->addModule ('admin/database'     , false, true , 0, 3, true);
	$UI->pages->addModule ('admin/users'        , false, true , 0, 4, true);
	$UI->pages->addModule ('admin/news'         , false, true , 0, 5, true);
	$UI->pages->addModule ('admin/pages'        , false, true , 0, 6, true);
	$UI->pages->addModule ('admin/extensions'   , false, true , 0, 7, true);
	$i10nMan = &$UI->i10nMan;
	$languages = $i10nMan->getAllSupportedLanguages ();
	foreach ($languages as $language) {
		$i10nMan->loadLanguage ($language);
		$UI->pages->addPage ('index', $language, $i10nMan->translate ('Home'), $i10nMan->translate ('This is the homepage.'));
		$UI->pages->addPage ('viewadmin', $language, $i10nMan->translate ('View admin'), '');
		$UI->pages->addPage ('logout', $language, $i10nMan->translate ('Logout'), '');
		$UI->pages->addPage ('register', $language, $i10nMan->translate ('Register'), '');
		$UI->pages->addPage ('usersettings', $language, $i10nMan->translate ('Change your settings'), '');
		$UI->pages->addPage ('admin/database', $language, $i10nMan->translate ('Database'), 'Here you change all database settings. WARNING: It is recommend that you don\'t change options here, only if you KNOW what you are doing.');
		$UI->pages->addPage ('admin/users', $language, $i10nMan->translate ('Users'), 'Here you can view all users. Ban them or remove them, make them admin or rempve from the admin.');
		$UI->pages->addPage ('admin/news', $language, $i10nMan->translate ('News'), 'Here you can view all news items. You can edit, remove or add items.');
		$UI->pages->addPage ('admin/general', $language, $i10nMan->translate ('General'), 'Here you edit all general options.');
		$UI->pages->addPage ('admin/addpage', $language, $i10nMan->translate ('Add page'), 'Add a page.');
		$UI->pages->addPage ('admin/index', $language, $i10nMan->translate ('Admin home'), 'This is the admin, here you edit all what you want.');
		$UI->pages->addPage ('admin/pages', $language, $i10nMan->translate ('Pages'), 'Here you can admin all pages.');
		$UI->pages->addPage ('admin/extensions', $language, $i10nMan->translate ('Extensions'), 'Here you can enable/disable extesnions.');
	}

	$username = addslashes ($_POST['admin-account']);
	$email = addslashes ($_POST['admin-email']);
	$password = addslashes ($_POST['admin-password']);
	$UI->user = new user ($UI->genDB);
	$settings = array ('language' => 'english', 'skin' => 'MorgOS Default', 'contentlanguage' => 'english');
	$UI->user->insertUser ($username, $email, $password, true, $settings);
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
