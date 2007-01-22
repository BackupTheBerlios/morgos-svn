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


include_once ('core/dbdrivermanager.class.php');

class DatabaseDriverManagerTest extends TestCase {

	function testFindAllDrivers () {
		// non existing dir
		$r = DatabaseDriverManager::findAllDriversInDirectory ('invalid');
		$this->assertTrue ($r->is (new Error ('PARAM_ISNOT_A_DIR', 'invalid')));
		// a file
		$r = DatabaseDriverManager::findAllDriversInDirectory ('index.php');
		$this->assertTrue ($r->is (new Error ('PARAM_ISNOT_A_DIR', 'index.php')));
		// where morgos drivers are located
		$r = DatabaseDriverManager::findAllDriversInDirectory ('core/dbdrivers');
		$this->assertFalse (isError ($r));
		global $installedDrivers;
		$this->assertEquals ($installedDrivers, 
			DatabaseDriverManager::getAllInstalledDrivers ());
	}
	
	function canDriverRun () {
		global $installedDrivers;
		foreach ($installedDriver as $driver) {
			$this->assertTrue (DatabaseDriverManager::canDriverRun ($driver));
		}
		
		$this->assertFalse (DatabaseDriverManager::canDriverRun ('NotInstalled'));
	}
	
	function testIsDriverInstalled () {
		global $installedDrivers;
		
		$this->assertTrue (DatabaseDriverManager::isDriverInstalled ('MySQL'));
		$this->assertTrue (DatabaseDriverManager::isDriverInstalled ('MySQLI'));
		$this->assertTrue (DatabaseDriverManager::isDriverInstalled ('PostgreSQL'));
		$this->assertFalse (DatabaseDriverManager::isDriverInstalled ('NotInstalled'));
	}
	
	function testAddDriver () {
		DatabaseDriverManager::addDriver (
			'CantRun', 'DatabaseDriverManagerTest', false);
		$this->assertFalse (DatabaseDriverManager::canDriverRun ('CantRun'));
		$r = DatabaseDriverManager::addDriver (
			'CantRun', 'DatabaseDriverManagerTest', false);
		$this->assertTrue ($r->is (
			new Error ('DBDRIVER_ALREADY_ADDED', 'CantRun')));
		
		$r = DatabaseDriverManager::addDriver (
			'InvalidClass', 'InvalidClassName', false);
		$this->assertTrue ($r->is (new Error ('DBDRIVERCLASS_NOTFOUND', 
			'InvalidClass', 'InvalidClassName')));
	}
	
	function testLoadDriver () {
		$r = DatabaseDriverManager::loadDriver ('DB');
		$this->assertTrue ($r->is (new Error ('INVALID_DATABASEDRIVER', 'DB')));
		
		$r = DatabaseDriverManager::loadDriver ('CantRun');
		$this->assertTrue ($r->is (
			new Error ('INVALID_DATABASEDRIVER', 'CantRun')));
	}
	
}
?>