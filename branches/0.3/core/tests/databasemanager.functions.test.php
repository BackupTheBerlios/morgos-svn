<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005-2007 MorgOS
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
/** \file databasemanager.functions.test.php
 * File that take care of the database modules tester
 *
 * @since 0.2
 * @author Nathan Samson
*/

include_once ('core/databasemanager.functions.php');
class databaseManagerTest extends TestCase {

	function setUp () {
		global $avModules;
		$this->availableModules = $avModules;
	}
	
	function testLoadModule () {
		$m = databaseLoadModule ('NOTEXISTINGMODULE');
		$this->assertEquals (new Error ('DATABASEMANAGER_MODULE_DOES_NOT_EXITS', 'NOTEXISTINGMODULE'), $m);
		
		$m = databaseLoadModule ('MySQL');
		$newMySQL = new mysqlDatabaseActions ();
		$this->assertEquals ($newMySQL, $m);
	}

	function testModuleGetAllModules () {
		$allModules = databaseGetAllModules ();
		$allModulesExpected = array ();
		$allModulesExpected['MySQL'] = 'mysqlDatabaseActions';
		//$allModulesExpected['XML'] = 'XMLDatabase';
		$allModulesExpected['EXISTINGBUTNOTWORKING'] = 'EMPTY';
		$allModulesExpected['MySQLI'] = 'mysqliDatabaseActions';
		$allModulesExpected['PostgreSQL'] = 'pgsqlDatabaseActions';
		$this->assertEquals ($allModulesExpected, $allModules);
		
		$allModules = databaseGetAllModules (true);
		$allModulesExpected = $this->availableModules;
		foreach ($allModulesExpected as $key => $value) {
			$allModulesExpected[$key] = $allModules[$key];
		}
		$this->assertEquals ($allModulesExpected, $allModules);
	}
	
	function testModuleExists () {
		$e = databaseModuleExists ('MySQL');
		$this->assertTrue ($e);
		
		$e = databaseModuleExists ('XMLSql');
		$this->assertFalse ($e);
		
		$e = databaseModuleExists ('EXISTINGBUTNOTWORKING');
		$this->assertTrue ($e);
		
		$e = databaseModuleExists ('EXISTINGBUTNOTWORKING', true);
		$this->assertFalse ($e);
	}
}


?>
