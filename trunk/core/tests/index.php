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
chdir ('../../');
include_once ('core/tests/base.php');

class MorgOSSuit extends TestSuite {

	function MorgOSSuit () {
		$this->setUp ();
	}

	function setUp () {
		global $dbModule;
		include_once ('core/varia.functions.php');
		include_once ('core/databasemanager.functions.php');
		include_once ('core/sqlwrapper.class.php');
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

		
		global $avModules;
		$availableModulesINI = explode (',', $testerOptions['dbAvailableModules']);
		foreach ($availableModulesINI as $value) {
			$value = trim ($value);
			$avModules[$value] = null;
		}
	
		include_once ('core/user/usermanager.class.php');
		include_once ('core/page/pagemanager.class.php');
		global $u, $p;
		$u = new userManager ($dbModule);
		$u->installAllTables ();	
		
		$p = new pageManager ($dbModule);
		$p->installAllTables ();
	
		$this->setName ('MorgOS automated Tester: results');
		global $php;
		if ($php == "4") {
			include_once ('core/tests/databasemanager.functions.test.php');
			include_once ('core/tests/config.class.test.php');
			include_once ('core/tests/usermanager.class.test.php');
			include_once ('core/tests/varia.functions.test.php');
			include_once ('core/tests/compatible.functions.test.php');
			include_once ('core/tests/pagemanager.class.test.php');
			//include_once ('core/tests/xmlsql.class.test.php');
		} elseif ($php == "5") {
			$this->addTestFile ('core/tests/databasemanager.functions.test.php');
			$this->addTestFile ('core/tests/sqlwrapper.class.test.php');
			$this->addTestFile ('core/tests/config.class.test.php');
			$this->addTestFile ('core/tests/usermanager.class.test.php');
			$this->addTestFile ('core/tests/varia.functions.test.php');
			$this->addTestFile ('core/tests/compatible.functions.test.php');
			$this->addTestFile ('core/tests/pagemanager.class.test.php');
		//	$this->addTestFile ('core/tests/xmlsql.class.test.php');
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
	$configsuite = new TestSuite ('configTest');
	$variasuite = new TestSuite ('variaTest');
	$compatiblesuite = new TestSuite ('compatibleTests');
	$pagemanagersuite = new TestSuite ('pageManagerTest');
	//$pagemanagersuite = new TestSuite ('XMLSQLTest');
	$GUI = new PHPUnit_GUI_HTML (array ($databasesuite, $variasuite, $compatiblesuite, $configsuite, $usersuite, $pagemanagersuite));
	$GUI->show ();
} elseif ($php == "5") {

	$suite->run ($suite->result);
	$suite->tearDown ();
}
$suite = null;
?>
