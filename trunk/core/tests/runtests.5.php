<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'MorgOSTests::main');
}
//chdir ('../../');
require_once ('core/tests/base.php');

class MorgOSTests {
	public static function main () {
		PHPUnit_TextUI_TestRunner::run (self::suite ());
	}
	
	public static function suite () {
		$suite = new PHPUnit_Framework_TestSuite ('MorgOS Test suite');
		include_once ('core/tests/dbdrivers/dbdrivertestgeneric.class.php');
		$suite->addTestFile ('core/tests/compatible.functions.test.php');
		$suite->addTestFile ('core/tests/varia.functions.test.php');
		$suite->addTestFile ('core/tests/config.class.test.php');
		global $avModules;		
		
		$suite->addTestFile ('core/tests/databasemanager.functions.test.php');

		$config = parse_ini_file ('core/tests/options.ini', true);
		foreach ($avModules as $module=>$object) {
			$mod = MorgOSTests::loadModuleFromConfig ($module, $config);
			if (isError ($a)) {
				echo 'SKIPPED: '.$module;
				continue;
			}
			MorgOSTests::removeAllTablesForModule ($mod);
		
			switch ($module) {
				case 'MySQL':
					$suite->addTestFile ('core/tests/dbdrivers/mysql.test.driver.class.php');
					break;
				case 'PostgreSQL': 
					$suite->addTestFile ('core/tests/dbdrivers/pgsql.test.driver.class.php');
					break;
			}
			$mod->disconnect ();
		}
		global $dbModule;
		$dbModule = MorgOSTests::loadModuleFromConfig ($config['defaultModule'], $config);
		MorgOSTests::removeAllTablesForModule ($dbModule);
		$suite->addTestFile ('core/tests/sqlwrapper.class.test.php');
		$suite->addTestFile ('core/tests/pagemanager.class.test.php');
		$suite->addTestFile ('core/tests/usermanager.class.test.php');
		//$suite->addTestFile ('core/tests/xmlsql.class.test.php');
		return $suite;
	}
	
	public static function loadModuleFromConfig ($module, $config) {
		$mod = databaseLoadModule ($module);	
		$mOpts = $config[$module];
		$a = $mod->connect ($mOpts['Host'], $mOpts['User'], 
			$mOpts['Password']);
		$a = $mod->selectDatabase ($mOpts['DatabaseName']);
		
		return $mod;
	}
	
	public static function removeAllTablesForModule ($module) {
		foreach ($module->getAllTables () as $tableName) {
			$sql = "DROP TABLE $tableName";
			$module->query ($sql);
		}
	}
}
if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main ();
}

?>