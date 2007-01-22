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
/** \file config.class.test.php
 * File that take care of testing config class.
 *
 * @since 0.2
 * @author Nathan Samson
*/

include_once ('core/config.class.php');
class configTest extends TestCase {
	function setUp () {
		$this->configurator = new configurator ();
	}

	function testCreateStringItem () {
		$string = new configItem ('aString', STRING);
		$r = $string->setDefaultValue ('Default');
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals ('Default', $string->getCurrentValue (), 'Default value fails.');
		
		$r = $string->setValue ('aValue');
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals ('aValue', $string->getCurrentValue (), 'Wrong value');
		$this->assertEquals ('aValue', $string->getInitialValue (), 'Wrong initial value');
		
		$r = $string->setValue ('secondValue');
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals ('secondValue', $string->getCurrentValue (), 'Wrong value 2');
		$this->assertEquals ('aValue', $string->getInitialValue (), 'Wrong initial value 2');
		
		$r = $string->setValue (false);
		$this->assertTrue (isError ($r), 'Unexpected un-error 1');
		
		$r = $string->setValue (1);
		$this->assertTrue (isError ($r), 'Unexpected un-error 2');
		
		$r = $string->setValue (3.7);
		$this->assertTrue (isError ($r), 'Unexpected un-error 3');
	}
	
	function testCreateBoolItem () {
		$bool = new configItem ('aBool', BOOL);
		$r = $bool->setDefaultValue (false);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (false, $bool->getCurrentValue (), 'Default value fails.');
		
		$r = $bool->setValue (true);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (true, $bool->getCurrentValue (), 'Wrong value');
		$this->assertEquals (true, $bool->getInitialValue (), 'Wrong initial value');
		
		$r = $bool->setValue (false);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (false, $bool->getCurrentValue (), 'Wrong value 2');
		$this->assertEquals (true, $bool->getInitialValue (), 'Wrong initial value 2');
		
		$r = $bool->setValue ('string');
		$this->assertTrue (isError ($r), 'Unexpected un-error 1');
		
		$r = $bool->setValue (1);
		$this->assertTrue (isError ($r), 'Unexpected un-error 2');
		
		$r = $bool->setValue (3.7);
		$this->assertTrue (isError ($r), 'Unexpected un-error 3');
	}
	
	function testCreateNumericItem () {
		$numeric = new configItem ('aNumeric', NUMERIC);
		$r = $numeric->setDefaultValue (0);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (0, $numeric->getCurrentValue (), 'Default value fails.');
		
		$r = $numeric->setValue (1);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (1, $numeric->getCurrentValue (), 'Wrong value');
		$this->assertEquals (1, $numeric->getInitialValue (), 'Wrong initial value');
		
		$r = $numeric->setValue (2);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (2, $numeric->getCurrentValue (), 'Wrong value 2');
		$this->assertEquals (1, $numeric->getInitialValue (), 'Wrong initial value 2');
		
		$r = $numeric->setValue ('string');
		$this->assertTrue (isError ($r), 'Unexpected un-error 1');
		
		$r = $numeric->setValue (false);
		$this->assertTrue (isError ($r), 'Unexpected un-error 2');
		
		$r = $numeric->setValue (3.7);
		$this->assertTrue (isError ($r), 'Unexpected un-error 3');
	}
	
	function testCreateRealItem () {
		$numeric = new configItem ('aReal', REAL);
		$r = $numeric->setDefaultValue (0);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (0, $numeric->getCurrentValue (), 'Default value fails.');
		
		$r = $numeric->setValue (1);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (1, $numeric->getCurrentValue (), 'Wrong value');
		$this->assertEquals (1, $numeric->getInitialValue (), 'Wrong initial value');
		
		$r = $numeric->setValue (3.141592);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (3.141592, $numeric->getCurrentValue (), 'Wrong value 2');
		$this->assertEquals (1, $numeric->getInitialValue (), 'Wrong initial value 2');
		
		$r = $numeric->setValue ('string');
		$this->assertTrue (isError ($r), 'Unexpected un-error 1');
		
		$r = $numeric->setValue (false);
		$this->assertTrue (isError ($r), 'Unexpected un-error 2');
		
		$r = $numeric->setValue (1.2e3);
		$this->assertFalse (isError ($r), 'Unexpected error 2');
		$this->assertEquals (1200.0, $numeric->getCurrentValue ());
		
		$r = $numeric->setValue (3.1E-3);
		$this->assertFalse (isError ($r), 'Unexpected error 3');
		$this->assertEquals (0.00310, $numeric->getCurrentValue ());
	}
	
	function testAddOption () {
		$newOption = new configItem ('/newItem', STRING);
		$r = $this->configurator->addOption ($newOption);
		$this->assertFalse (isError ($r), 'Unexpected error');		
		
		$newOption = new configItem ('/newItem', STRING);
		$r = $this->configurator->addOption ($newOption);
		$this->assertEquals (new Error ('CONFIGURATOR_OPTION_EXISTS', '/newItem'), $r);
		
		$newOption = new configItem ('/newItem', BOOL);
		$r = $this->configurator->addOption ($newOption);
		$this->assertEquals (new Error ('CONFIGURATOR_OPTION_EXISTS', '/newItem'), $r);
	}
	
	function testRemoveOption () {
		$newOption = new configItem ('/newItem', STRING);
		$r = $this->configurator->addOption ($newOption);
		$this->assertFalse (isError ($r), 'Unexpected error 1');		
		
		$r = $this->configurator->removeOption ($newOption);
		$this->assertFalse (isError ($r), 'Unexpected error 2');		
		
		$r = $this->configurator->removeOption ($newOption);
		$this->assertEquals (new Error ('CONFIGURATOR_OPTION_DOESNT_EXISTS', '/newItem'), $r);
	}
	
	function testGetItem () {
		$string =  new configItem ('/aString', STRING);
		$string->setValue ('string');
		$this->configurator->addOption ($string);
		$this->assertEquals ('string', $this->configurator->getStringItem ('/aString'));
		
		$bool = new configItem ('/aBool', BOOL);
		$bool->setValue (false);
		$r = $this->configurator->addOption ($bool);
		$this->assertEquals (false, $this->configurator->getBoolItem ('/aBool'));
		
		$numeric =  new configItem ('/aNumeric', NUMERIC);
		$numeric->setValue (7);
		$this->configurator->addOption ($numeric);
		$this->assertEquals (7, $this->configurator->getNumericItem ('/aNumeric'));
		
		$real =  new configItem ('/aReal', REAL);
		$real->setValue (3.22);
		$this->configurator->addOption ($real);
		$this->assertEquals (3.22, $this->configurator->getRealItem ('/aReal'));
		
		$this->assertEquals (new Error ('CONFIGURATOR_ITEM_DOESNT_EXISTS', '/aReal'), 
			$this->configurator->getBoolItem ('/aReal'), "Wrong error");
	}
	
	function testGetArrayItem () {
		$this->configurator->loadConfigFile ('core/tests/options.php');
		$string =  new configItem ('/anArray/aString', STRING);
		$string->setValue ('anArrayString');
		
		$real =  new configItem ('/anArray/aReal', REAL);
		$real->setValue (2.1);
		$a = $this->configurator->getArrayItem ('/anArray');
		$this->assertEquals (array ('aString'=>$string, 'aReal'=>$real), $a);
	}
	
	function testLoadFromFile () {
		$this->configurator->loadConfigFile ('core/tests/options.php');
		$this->assertEquals (1.0, $this->configurator->getRealItem ('/aReal'));
		$this->assertEquals (1, $this->configurator->getNumericItem ('/aNumeric'));
		$this->assertEquals (0, $this->configurator->getNumericItem ('/aZeroNumeric'));
		$this->assertEquals (false, $this->configurator->getBoolItem ('/aBool'));
		$this->assertEquals ("string", $this->configurator->getStringItem ('/aString'));
	}
	
	function testLoadFromWrongFile () {
		$a = $this->configurator->loadConfigFile ('core/tests/UNEXISTING.php');
		$this->assertTrue ($a->is ('CONFIG_FILE_NOT_FOUND'));
		
		// this seems to  be untestable
		//$a = $this->configurator->loadConfigFile ('core/tests/unreadable.php');
		//$this->assertTrue ($a->is ('CONFIG_FILE_NOT_READABLE'));
	}
	
	function testCheckType () {
		$a = checkType (7.324, 'Float');
		$this->assertTrue ($a->is ('TYPE_NOT_RECOGNIZED'));
	}
	
	function testExistsItem () {
		$this->configurator->loadConfigFile ('core/tests/options.php');
		$this->assertTrue ($this->configurator->existsItem ('/aBool'));
		$this->assertTrue ($this->configurator->existsItem ('/aReal'));
		$this->assertTrue ($this->configurator->existsItem ('/aNumeric'));
		$this->assertTrue ($this->configurator->existsItem ('/aString'));
	}
	
	function testGetStringValue () {
		$this->configurator->loadConfigFile ('core/tests/options.php');
		$real = $this->configurator->getItem ('/aReal', REAL);
		$this->assertEquals ('1.0', $real->getStringValue ());
		$string = $this->configurator->getItem ('/aString', STRING);
		$this->assertEquals ('\'string\'', $string->getStringValue ());
		$int = $this->configurator->getItem ('/aNumeric', NUMERIC);
		$this->assertEquals ('1', $int->getStringValue ());
		$bool = $this->configurator->getItem ('/aBool', BOOL);
		$this->assertEquals ('false', $bool->getStringValue ());
		$bool->setValue (true);
		$this->assertEquals ('true', $bool->getStringValue ());
	}
	
	function testUserOptions () {
		$r = $this->configurator->addUserSetting ('stringOption', STRING, 'zero');
		$this->assertEquals ('zero', $r);
		
		$r = $this->configurator->addUserSetting ('intOption', NUMERIC, 3);
		$this->assertEquals (3, $r);
		
		$_GET['floatOption'] = 7.1;
		$r = $this->configurator->addUserSetting ('floatOption', REAL, 3.2);
		$this->assertEquals (7.1, $r);
		
		$_COOKIE['boolOption'] = true;
		$r = $this->configurator->addUserSetting ('boolOption', BOOL, false);
		$this->assertEquals (true, $r);
		
		$_GET['anotherString'] = 'getval';
		$_COOKIE['anotherString'] = 'cookieval';
		$r = $this->configurator->addUserSetting ('anotherString', STRING, 
			'defaultVal');
		$this->assertEquals ('getval', $r);
		
		$this->assertEquals (7.1, 
			$this->configurator->getRealItem ('/user/floatOption'));
	}
}
?>