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
/** \file i18n.class.test.php
 * File that take care of testing translation tests
 *
 * @since 0.3
 * @author Nathan Samson
*/

include_once ('core/i18n.class.php');
class i18nTest extends TestCase {
	
	var $_i18n;
	
	function setUp () {
		global $i18nManager;
		if (! $i18nManager) {
			$i18nManager = new localizer ();
		}
		$this->_i18n = &$i18nManager;
	}
	
	function testLoadLanguage () {
		$r = $this->_i18n->loadLanguage ('not_found', 'core/tests');
		$this->assertTrue (isError ($r));
		$this->assertTrue ($r->is ('LANGUAGE_FILE_NOT_FOUND'));
		
		$r = $this->_i18n->loadLanguage ('i18n_example', 'core/tests');
		$this->assertFalse (isError ($r));
		$this->assertEquals ('i18n_example', $this->_i18n->getLanguage ());
	}
	
	function testTranslate () {
		$this->assertEquals ('String One', $this->_i18n->translate ('String 1'));
		$this->assertEquals ('Do Test to achieve parameter2', 
			$this->_i18n->translate ('%1 String %2', array ('Test', 'parameter2')));
		$this->assertEquals ('Do Test to achieve parameter2', 
			$this->_i18n->translate ('%2 String %1', array ('Test', 'parameter2')));
		$this->assertEquals ('Do Parameter2 to achieve param1', 
			$this->_i18n->translate (
				'%2 String %1 order change', array ('param1', 'Parameter2')));
	}
	
	function testTranslateErrors () {
		$r = $this->_i18n->translateError (new Error ('UKNOWN_ERROR', 'param1'));
		$this->assertEquals ('Unexpected error. (UKNOWN_ERROR)', $r);
		
		$this->_i18n->addError ('FILL_IN', 'Error, please fill in %1');
		$r = $this->_i18n->translateError (new Error ('FILL_IN', 'name'));
		$this->assertEquals ('Error, name is not filled in', $r);
		
		$this->_i18n->addError ('WITHOUT_PARAM', 'Error, without param');
		$r = $this->_i18n->translateError (new Error ('WITHOUT_PARAM'));
		$this->assertEquals ('Without param test', $r);
	}
}
?>
