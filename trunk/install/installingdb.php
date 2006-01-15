<?php
	// create the database
	// TODO: add prefix config
	define ('TBL_PREFIX', 'morgos_');
	define ('TBL_MODULES', TBL_PREFIX . 'modules');
	define ('TBL_PAGES', TBL_PREFIX . 'userpages');
	include_once ('core/user.class.php');
	include_once ('core/language.class.php');
	include_once ('core/database.class.php');
	include_once ('core/config.class.php');
	include_once ('core/pages.class.php');

	$SQL = file_get_contents ('install/sql/news.sql');
	$SQL .= file_get_contents ('install/sql/pages.sql');
	$SQL .= file_get_contents ('install/sql/users.sql');
	$SQL = str_replace ('%prefix%', 'morgos_', $SQL);

	$i10nMan = new languages ('languages/');
	$DBMan = new genericDatabase ($i10nMan);
	$config = new config ($i10nMan);
	$config->addConfigItemsFromFile ('site.config.php');
	$DB = $DBMan->load ($config->getConfigItem ('/database/type'));
	$DB->connect ($config->getConfigItem ('/database/host'),
		$config->getConfigItem ('/database/user'), 
		$config->getConfigItem ('/database/password'));
	$DB->select_db ($config->getConfigItem ('/database/name'));
	$arrayOfSQL = explode (';', $SQL);
	foreach ($arrayOfSQL as $query) {
		$query = trim ($query);
		if (empty ($query)) {
			continue;
		}
		$result = $DB->query ($query);
		if ($result === false) {
			trigger_error ('ERROR: ' . $i10nMan->translate ('Query'));
		}
	}
	$pages = new pages ($DB, $i10nMan);
	$pages->addModule ('index'              , false, false, 1, 0, true);
	$pages->addModule ('register'           , false, false, 0, 0, false);
	$pages->addModule ('forgotpass'         , false, false, 0, 0, false);
	$pages->addModule ('user'			, true , false, 2, 0, false, NULL, false); //nolink is true
	$pages->addModule ('usersettings'       , true , false, 1, 0, false, 'user');
	$pages->addModule ('logout'             , true , false, 3, 0, false, 'user');
	$pages->addModule ('viewadmin'          , true , true , 2, 0, false, 'user');
	$pages->addModule ('admin/addpage'      , false, true , 0, 0, true);
	$pages->addModule ('admin/editpage'      , false, true , 0, 0, true);
	$pages->addModule ('admin/index'        , false, true , 0, 1, true);
	$pages->addModule ('admin/general'      , false, true , 0, 2, true);
	$pages->addModule ('admin/database'     , false, true , 0, 3, true);
	$pages->addModule ('admin/users'        , false, true , 0, 4, true);
	$pages->addModule ('admin/news'         , false, true , 0, 5, true);
	$pages->addModule ('admin/pages'        , false, true , 0, 6, true);
	$pages->addModule ('admin/extensions'   , false, true , 0, 7, true);

	$languages = $i10nMan->getAllSupportedLanguages ();
	foreach ($languages as $language) {
		$i10nMan->loadLanguage ($language);
		$pages->addPage ('index', $language, $i10nMan->translate ('Home'), $i10nMan->translate ('This is the homepage.'));
		$pages->addPage ('viewadmin', $language, $i10nMan->translate ('View admin'), '');
		$pages->addPage ('logout', $language, $i10nMan->translate ('Logout'), '');
		$pages->addPage ('register', $language, $i10nMan->translate ('Register'), '');
		$pages->addPage ('usersettings', $language, $i10nMan->translate ('Change your settings'), '');
		$pages->addPage ('user', $language, $i10nMan->translate ('User'), '');
		$pages->addPage ('admin/database', $language, $i10nMan->translate ('Database'), 'Here you change all database settings. WARNING: It is recommend that you don\'t change options here, only if you KNOW what you are doing.');
		$pages->addPage ('admin/users', $language, $i10nMan->translate ('Users'), 'Here you can view all users. Ban them or remove them, make them admin or rempve from the admin.');
		$pages->addPage ('admin/news', $language, $i10nMan->translate ('News'), 'Here you can view all news items. You can edit, remove or add items.');
		$pages->addPage ('admin/general', $language, $i10nMan->translate ('General'), 'Here you edit all general options.');
		$pages->addPage ('admin/addpage', $language, $i10nMan->translate ('Add page'), 'Add a page.');
		$pages->addPage ('admin/editpage', $language, $i10nMan->translate ('Edit page'), 'Edit a page.');
		$pages->addPage ('admin/index', $language, $i10nMan->translate ('Admin home'), 'This is the admin, here you edit all what you want.');
		$pages->addPage ('admin/pages', $language, $i10nMan->translate ('Pages'), 'Here you can admin all pages.');
		$pages->addPage ('admin/extensions', $language, $i10nMan->translate ('Extensions'), 'Here you can enable/disable extesnions.');
	}

	$username = addslashes ($_POST['admin-account']);
	$email = addslashes ($_POST['admin-email']);
	$password = addslashes ($_POST['admin-password']);
	$user = new user ($DB, $i10nMan);
	$settings = array ('language' => 'english', 'skin' => 'MorgOS Default', 'contentlanguage' => 'english');
	$user->insertUser ($username, $email, $password, true, $settings);
?>
<?php echo '<?xml version="1.0"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $i10nMan->translate ('MorgOS Installation Wizard Step 5'); ?></title>
	</head>
	<body>
		<h1><?php echo $i10nMan->translate ('Install wizard MorgOS Step 5: Installation of database'); ?></h1>
		<?php showAllErrors (); ?>
		<form action='./index.php' method='post'>
			<div>
				<?php echo $i10nMan->translate ('If you do not see any errors above the installation succeed. If you see any error check that MorgOS was not installed before. Copy all errors and post them as a bug on our website.'); ?>
				<?php echo $i10nMan->translate ('Installation is done.'); ?>
				<?php echo $i10nMan->translate ('Remove the dir "install/" and file "install.php" and press "next" to go to the site.'); ?>
			</div>
			<input type='submit' value='<?php echo $i10nMan->translate ('Next'); ?>' />
		</form>
	</body>
</html>
