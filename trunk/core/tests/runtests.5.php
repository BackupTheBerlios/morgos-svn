<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'MorgOSTests::main');
}
//chdir ('../../');
require_once ('core/tests/base.php');

class MorgOSTests {
	public static function main () {
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}
	
	public static function suite () {
		$suite = new PHPUnit_Framework_TestSuite('MorgOS Test suite');
		
		$suite->addTestFile ('core/tests/compatible.functions.test.php');
		$suite->addTestFile ('core/tests/varia.functions.test.php');
		$suite->addTestFile ('core/tests/config.class.test.php');
		$suite->addTestFile ('core/tests/databasemanager.functions.test.php');
		$suite->addTestFile ('core/tests/sqlwrapper.class.test.php');
		//$suite->addTestFile ('core/tests/pagemanager.class.test.php');
		$suite->addTestFile ('core/tests/usermanager.class.test.php');
		//$suite->addTestFile ('core/tests/xmlsql.class.test.php');
		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}

?>