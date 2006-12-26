<?php
include_once ('core/tests/compwrapper.class.php');

$testerOptions = parse_ini_file ('core/tests/options.ini');

global $avModules;
$availableModulesINI = explode (',', $testerOptions['dbAvailableModules']);
foreach ($availableModulesINI as $value) {
	$value = trim ($value);
	$avModules[$value] = null;
}

include_once ('core/varia.functions.php');
include_once ('core/databasemanager.functions.php');

include_once ('core/sqlwrapper.class.php');
include_once ('core/user/usermanager.class.php');
include_once ('core/page/pagemanager.class.php');

function loadSuite (&$suite) {
	include_once ('core/tests/dbdrivers/dbdrivertestgeneric.class.php');
	$suite->addTestFile ('core/tests/compatible.functions.test.php');
	$suite->addTestFile ('core/tests/varia.functions.test.php');
	$suite->addTestFile ('core/tests/config.class.test.php');
	$suite->addTestFile ('core/tests/i18n.class.test.php');
	global $avModules;		
	
	$suite->addTestFile ('core/tests/databasemanager.functions.test.php');

	$config = parse_ini_file ('core/tests/options.ini', true);
	foreach ($avModules as $module=>$object) {
		$mod = MorgOSTests::loadModuleFromConfig ($module, $config);
		if (isError ($mod)) {
			continue;
		}
		MorgOSTests::removeAllTablesForModule ($mod);
		$mod->disconnect ();
	
		switch ($module) {
			case 'MySQL':
				$suite->addTestFile ('core/tests/dbdrivers/mysql.test.driver.class.php');
				break;
			case 'MySQLI':
				$suite->addTestFile ('core/tests/dbdrivers/mysqli.test.driver.class.php');
				break;
			case 'PostgreSQL': 
				$suite->addTestFile ('core/tests/dbdrivers/pgsql.test.driver.class.php');
				break;
		}
	}
	global $dbModule;
	$dbModule = MorgOSTests::loadModuleFromConfig ($config['defaultModule'], $config);
	MorgOSTests::removeAllTablesForModule ($dbModule);
	$suite->addTestFile ('core/tests/sqlwrapper.class.test.php');
	$suite->addTestFile ('core/tests/pagemanager.class.test.php');
	$suite->addTestFile ('core/tests/usermanager.class.test.php');
	//$suite->addTestFile ('core/tests/xmlsql.class.test.php');
}

class MorgOSTests {	
	function loadModuleFromConfig ($module, $config) {
		$mod = databaseLoadModule ($module);
		
		if (isError ($mod)) {
			return $mod;
		}
		
		$mOpts = $config[$module];
		$a = $mod->connect ($mOpts['Host'], $mOpts['User'], 
			$mOpts['Password'], $mOpts['DatabaseName']);
		if (isError ($a)) {
			return $a;
		}
		
		return $mod;
	}
	
	function removeAllTablesForModule ($module) {
		foreach ($module->getAllTables () as $tableName) {
			$sql = "DROP TABLE $tableName";
			$module->query ($sql);
		}
	}
}


?>