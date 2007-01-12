<?php
/*This file is used for the development*/

if (file_exists ('config.php')) {
	include_once ('interface/morgos.class.php');

	/* lets spoof $_POST
	 * two problems with this approach:
	 * enableUsers is reset to true (if config.php is writable)
	 * adminUser is reset to test
	*/
	$c = new configurator ();
	$c->loadConfigFile ('config.php');
	$_POST['action'] = 'installerInstall';
	$_POST['siteName'] = $c->getStringItem ('/site/title');
	$_POST['siteDefaultLanguage'] = $c->getStringItem ('/site/default_language');
	$_POST['databaseModule'] = $c->getStringItem ('/databases/module');
	$_POST['databaseHost'] = $c->getStringItem ('/databases/host');
	$_POST['databaseUser'] = $c->getStringItem ('/databases/user');
	$_POST['databasePassword'] = $c->getStringItem ('/databases/password');
	$_POST['databaseName'] = $c->getStringItem ('/databases/database');
	$_POST['databasePrefix'] = $c->getStringItem ('/databases/table_prefix');
	$_POST['adminLogin'] = 'admin';
	$_POST['adminPassword1'] = 'test';
	$_POST['adminPassword2'] = 'test';
	$_POST['adminMail'] = 'someuser@somemail.com';
	// lets hack some more
	$_SERVER['REQUEST_METHOD'] = 'POST';
	
	$dbDriver = databaseLoadModule ($_POST['databaseModule']);
	$dbDriver->connect ($_POST['databaseHost'], 
		$_POST['databaseUser'], $_POST['databasePassword'], $_POST['databaseName']);
		
	foreach ($dbDriver->getAllTables () as $t) {
		if (ereg ('^'.$_POST['databasePrefix'], $t)) {
			$sql = "DROP TABLE $t";
			$dbDriver->query ($sql);
		}
	}
	
	$morgos = new BaseMorgos ();
	$morgos->run ();
	header ('Location: index.php'); 
	// header to index.php even if config.php is unwritable
} else {
	die ('config.php not found');
}

?>