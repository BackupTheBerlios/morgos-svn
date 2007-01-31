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
include_once ('interface/skinmanager.class.php');

class MockSmartyObject {
	var $compiler_dir;
	var $cache_dir;
	var $template_dir = array ();
	
	function assign ($name, $value) {
	}
}

class MockPluginAPISkin {
	var $smarty;
	function MockPluginAPISkin () {
		$this->smarty = new MockSmartyObject ();
	}	
	
	function &getSmarty () {
		return $this->smarty;
	}
}

class SkinManagerTest extends TestCase {
	var $_skinM;	
	
	function setUp () {
		global $skinM;
		global $pAPI;
		if (! $skinM) {
			$pAPI = new MockPluginAPISkin ();
			$skinM = new SkinManager ($pAPI);
			@rmdir ('skins_c/working');	
		}
		$this->_skinM = &$skinM;
		$this->_pAPI = &$pAPI;
	}
	
	function testFindAllSkins () {
		$r = $this->_skinM->findAllSkins ('heywhatADirectory');
		$this->assertTrue ($r->is ('DIRECTORY_NOT_FOUND'));
		
		$r = $this->_skinM->findAllSkins ('interface/tests/skins');
		$this->assertFalse (isError ($r));
	}
	
	function testSkinExists () {
		$this->assertFalse ($this->_skinM->existsSkin ('{wrong-id}'));
		$this->assertTrue (
			$this->_skinM->existsSkin ('{331d1a7c-bf6d-4527-8b1a-b0ee08077a76}'));
	}
	
	function testGetFoundSkinsArray () {
		$expArray = array (
			array ('ID'=>'{331d1a7c-bf6d-4527-8b1a-b0ee08077a76}',
				'Name'=>'MorgOS Test theme'));
		$this->assertEquals ($expArray, $this->_skinM->getFoundSkinsArray ());
	}
	
	function testLoadSkin () {
		$r = $this->_skinM->loadSkin ('{what-an-id}');
		$this->assertTrue($r->is ('SKINID_NOT_FOUND'));

		$r = $this->_skinM->loadSkin ('{331d1a7c-bf6d-4527-8b1a-b0ee08077a76}');
		$this->assertFalse (isError ($r));
		$this->assertEquals ('skins_c/working', 
			$this->_pAPI->smarty->compile_dir);
	}
}
?>