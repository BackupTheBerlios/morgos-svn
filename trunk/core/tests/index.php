<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005-2006 MorgOS
 * This program is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * any later version.
 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Library General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. 
*/
/** \file core/tests/index.php
 * Suite for the tests
 *
 * @since 0.2
 * @author Nathan Samson
*/

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
	require_once 'PHPUnit2/Framework/TestSuite.php';
	require_once 'PHPUnit2/Util/TestDox/ResultPrinter/HTML.php';
	
	class TestSuite extends PHPUnit2_Framework_TestSuite  {
	}
	
	class TestCase extends PHPUnit2_Framework_TestCase {
	}
	
	class TestResult extends PHPUnit2_Framework_TestResult {
	}
} else {
	die ('Unsupported PHP version');
}

chdir ('../../');
if ($php == "5") {
	require_once 'core/tests/testoutput.php';
}
class MorgOSSuit extends TestSuite {

	function MorgOSSuit () {
		$this->setUp ();
	}

	function setUp () {
		global $dbModule;
		include_once ('core/varia.functions.php');
		include_once ('core/databasemanager.functions.php');
		$testerOptions = parse_ini_file ('core/tests/options.ini');
		$dbModule = databaseLoadModule ($testerOptions['dbModule']);
		if (isError ($dbModule)) {
			die ('Can\'t load database module, check your settings.');
		}
		$r = $dbModule->connect ($testerOptions['dbHost'], $testerOptions['dbUser'], $testerOptions['dbPass']);
		if (isError ($r)) {
			die ('Can\' connect to database, check your settings.');
		}
		$r = $dbModule->selectDatabase ($testerOptions['dbDatabaseName']);
		if (isError ($r)) {
			die ('Wrong databasename, check your settings.');
		}
		foreach ($dbModule->getAllTables () as $tableName) {
			$r = $dbModule->query ("DROP TABLE $tableName");
			if (isError ($r)) {
				var_dump ($r);
				exit ();
			}
		}
		$queries = file_get_contents ("core/tests/database.sql");
		$a = split (';', $queries);
		foreach ($a as $sql) {
			if (trim ($sql) != '') {
				$r = $dbModule->query ($sql);
				if (isError ($r)) {
					var_dump ($r);
					exit ();
				}
			}
		}
		
		global $avModules;
		$availableModulesINI = explode (',', $testerOptions['dbAvailableModules']);
		foreach ($availableModulesINI as $value) {
			$avModules[$value] = null;
		}
	
		include_once ('core/user/usermanager.class.php');
		global $u;
		$u = new userManager ($dbModule);	
	
		$this->setName ('MorgOS automated Tester: results');
		global $php;
		if ($php == "4") {
			include_once ('core/tests/databasemanager.functions.test.php');
			include_once ('core/tests/usermanager.class.test.php');
			//var_dump ();
			$this->addTest (new databaseManagerTest ('databaseManagerTest'));
			$this->addTest (new userManagerTest ('testNewUser'));
		} elseif ($php == "5") {
			$this->addTestFile ('core/tests/databasemanager.functions.test.php');
			$this->addTestFile ('core/tests/usermanager.class.test.php');
		}

		$this->result = new TestResult;
		if ($php == "5") {
			$this->result->addListener(new SimpleTestListener);
		}

		$this->dbModule = $dbModule;
	}
	
	function tearDown () {
		$this->dbModule->disconnect ();
	}

}

$suite = new MorgOSSuit ();
if ($php == "4") {
	require_once ('PHPUnit/GUI/HTML.php');
	$databasesuite = new TestSuite ('databaseManagerTest');
	$usersuite = new TestSuite ('userManagerTest');
	$suite->addTestSuite ($databasesuite);
	$suite->addTestSuite ($usersuite);
	$GUI = new PHPUnit_GUI_HTML (array ($databasesuite, $usersuite));
	$GUI->show ();
} elseif ($php == "5") {
	$suite->run ($suite->result);
	$suite->tearDown ();
}
$suite = null;
?>
