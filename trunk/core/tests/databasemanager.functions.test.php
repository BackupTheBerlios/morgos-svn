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
/** \file databasemanager.functions.test.php
 * File that take care of the database modules tester
 *
 * @since 0.2
 * @author Nathan Samson
 * @license GPL
*/

include ('core/databasemanager.functions.php');
class databaseManagerTest extends PHPUnit2_Framework_TestCase {

	function setUp () {
		//$this->setName ('DatabaseManager');
	}
	
	function testLoadModule () {
		$m = databaseLoadModule ('NOTEXISTINGMODULE');
		$this->assertSame ("ERROR_DATABASEMANAGER_MODULE_DOES_NOT_EXITS NOTEXISTINGMODULE", $m);
		
		$m = databaseLoadModule ('MySQL');
		$this->assertSame (null, $m);
	}

	function testModuleGetAllModules () {
		$allModules = databaseGetAllModules ();
		$allModulesExpected = array ();
		$allModulesExpected['MySQL'] = 'mysqlDatabaseActions';
		$allModulesExpected['EXISTINGBUTNOTWORKING'] = 'EMPTY';
		$this->assertSame ($allModulesExpected, $allModules);
		
		$allModules = databaseGetAllModules (true);
		$allModulesExpected = array ();
		$allModulesExpected['MySQL'] = 'mysqlDatabaseActions';	
		$this->assertSame ($allModulesExpected, $allModules);
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

$databaseManagerTests = new databaseManagerTest ();

?>