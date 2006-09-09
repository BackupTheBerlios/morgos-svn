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
/** \file pluginmanager.class.test.php
 * File that take care of testing pluginmanager class.
 *
 * @since 0.2
 * @author Nathan Samson
*/
define ('MORGOS_VERSION', '0.2');
include_once ('core/varia.functions.php');
include_once ('interface/pluginmanager.class.php');

class pluginAPI {
	var $_value;
	
	function pluginAPI () {
		$this->_value = 0;
	}

	function returnsTrue () {
		return true;
	}
	
	function returnsFalse () {
		return false;
	}

}

class pluginManagerTest extends TestCase {
	var $_pluginManager;
	var $_pluginAPI;

	function setUp () {
		$this->_pluginAPI = new pluginAPI ();
		$this->_pluginManager = new pluginManager ($this->_pluginAPI);
		$this->_pluginManager->findAllPlugins ('interface/tests/plugins');
		
		$this->_dataTesterPlugin = new dataTesterPlugin ('interface/tests/plugins/datatester');
		$this->_notCompatible = new notCompatible ('interface/tests/plugins/versiontolow');
		$this->_notCompatible2 = new notCompatible2 ('interface/tests/plugins/versiontohigh');
		$this->_notCompatibleWithPHP = new notCompatibleWithPHP ('interface/tests/plugins/incompatiblewithphp');
	}
	
	function testGetAllFoundPlugins () {		
		$this->assertEquals (
			array (
				$this->_dataTesterPlugin->getID ()=>$this->_dataTesterPlugin, 
				$this->_notCompatible->getID ()=>$this->_notCompatible,
				$this->_notCompatible2->getID ()=>$this->_notCompatible2,
				$this->_notCompatibleWithPHP->getID ()=>$this->_notCompatibleWithPHP), 
			$this->_pluginManager->getAllFoundPlugins ());
	}

	function testLoadPlugin () {
		$r = $this->_pluginManager->setPluginToLoad ($this->_dataTesterPlugin->getID ());
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->_pluginManager->loadPlugins ();
		$this->_dataTesterPlugin->load ($this->_pluginAPI);
		$this->assertEquals (array ($this->_dataTesterPlugin->getID ()=>$this->_dataTesterPlugin), $this->_pluginManager->getAllLoadedPlugins (), 'Wrong result returned');
	}
	
	function testDataTesterPlugin () {
		$r = $this->_pluginManager->setPluginToLoad ($this->_dataTesterPlugin->getID ());
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->_pluginManager->loadPlugins ();
		$a = $this->_pluginManager->getLoadedPlugin ($this->_dataTesterPlugin->getID ());
		$this->assertTrue ($a->testReturnTrue ());
		$this->assertFalse ($a->testReturnFalse ());
		$this->assertEquals (0, $this->_pluginAPI->_value);
		$a->testData1 ();
		$this->assertEquals (1, $this->_pluginAPI->_value);
		$a->testData2 ();
		$this->assertEquals (2, $this->_pluginAPI->_value);
	}
	
	
	function testIsCompatible () {
		$r = $this->_pluginManager->setPluginToLoad ($this->_notCompatible->getID ());
		$this->assertEquals (new Error ('PLUGINMANAGER_MINIMALVERSION_NOT_REACHED', '0.3'), $r, 'Min version check fails');
		
		$r = $this->_pluginManager->setPluginToLoad ($this->_notCompatible2->getID ());
		$this->assertEquals (new Error ('PLUGINMANAGER_MAXVERSION_REACHED', '0.1'), $r, 'Max version check fails');
	}
			
	function testIsPHPCompatible () {
		$r = $this->_pluginManager->setPluginToLoad ($this->_notCompatibleWithPHP->getID ());
		$this->assertEquals (new Error ('PLUGINMANAGER_NOT_COMPATIBLE'), $r, 'PHP check fails');
	}
}
?>