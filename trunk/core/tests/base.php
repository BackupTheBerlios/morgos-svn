<?php
if (version_compare (PHP_VERSION, '5', '<=')) {
	$php = "4";
	require_once 'PHPUnit/TestSuite.php';
	//require_once 'PHPUnit/Util/TestDox/ResultPrinter/HTML.php';
	
	class TestSuite extends PHPUnit_TestSuite  {
	}
	
	class TestCase extends PHPUnit_TestCase {
	}
	
	class TestResult extends PHPUnit_TestResult {
	}
} elseif (version_compare (PHP_VERSION, '5', '>=')) {
	$php = "5";
	require_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';
	
	class TestSuite extends PHPUnit_Framework_TestSuite  {
	}
	
	class TestCase extends PHPUnit_Framework_TestCase {
	}
	
	class TestResult extends PHPUnit_Framework_TestResult {
	}
} else {
	die ('Unsupported PHP version');
}

$testerOptions = parse_ini_file ('core/tests/options.ini');

global $avModules;
$availableModulesINI = explode (',', $testerOptions['dbAvailableModules']);
foreach ($availableModulesINI as $value) {
	$value = trim ($value);
	$avModules[$value] = null;
}

include_once ('core/varia.functions.php');
include_once ('core/databasemanager.functions.php');

$dbModule = databaseLoadModule ($testerOptions['dbModule']);

$r = $dbModule->connect ($testerOptions['dbHost'], $testerOptions['dbUser'], $testerOptions['dbPass']);

$r = $dbModule->selectDatabase ($testerOptions['dbDatabaseName']);

foreach ($dbModule->getAllTables () as $tableName) {
	$r = $dbModule->query ("DROP TABLE $tableName");
	if (isError ($r)) {
		var_dump ($r);
		exit ();
	}
}

include_once ('core/sqlwrapper.class.php');
include_once ('core/user/usermanager.class.php');
include_once ('core/page/pagemanager.class.php');
global $u, $p;
$u = new userManager ($dbModule);
$u->installAllTables ();	
	
$p = new pageManager ($dbModule);
$p->installAllTables ();

/*if ($php == "5") {
	require_once 'core/tests/testoutput.php';
}*/
?>