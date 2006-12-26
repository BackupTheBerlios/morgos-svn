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

include_once ('core/varia.functions.php');
include_once ('interface/actionmanager.class.php');

class inputTest extends TestCase {
	function testStringInput () {
		$inp = new StringInput ('aString');
		$from = array ();
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('EMPTY_INPUT'));
		$from = array ('aString'=>'');
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('EMPTY_INPUT'));
		$from = array ('aString'=>'testInput');
		$r = $inp->getValue ($from);
		$this->assertEquals ('testInput', $r);
	}
	
	function testIntInput () {
		$inp = new IntInput ('anInt');
		$from = array ('anInt'=>'88');
		$r = $inp->getValue ($from);
		$this->assertSame (88, $r);
	}
	
	function testEmailInput () {
		$inp = new EmailInput ('anEmail');
		$from = array ('anEmail'=>'anemail@domain.com');
		$r = $inp->checkInput ($from);
		$this->assertFalse (isError ($r));
		
		$from = array ('anEmail'=>'anemail+spam@domain.com');
		$r = $inp->checkInput ($from);
		$this->assertFalse (isError ($r));
		
		$from = array ('anEmail', '');
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('EMPTY_INPUT'));		
		
		$from = array ('anEmail'=>'anemailATdomain.com');
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('INVALID_EMAIL'));
		
		$from = array ('anEmail'=>'anemail@domain.combe');
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('INVALID_EMAIL'));
		
		$from = array ('anEmail'=>'anemail@domaincom');
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('INVALID_EMAIL'));
	}
	
	function testEnumInput () {
		$inp = new EnumInput ('anEnum', array ('a', 'b', 'C'));
		$from = array ('anEnum'=>'a');
		$r = $inp->checkInput ($from);
		$this->assertFalse (isError ($r));
		
		$from = array ('anEnum'=>'C');
		$r = $inp->checkInput ($from);
		$this->assertFalse (isError ($r));
		
		$from = array ('anEnum'=>'c');
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('INVALID_CHOICE'));
	}
	
	function testBoolInput () {
		$inp = new BoolInput ('aBool');
		$from = array ('aBool'=>'Y');
		$this->assertTrue ($inp->getValue ($from));
		
		$from = array ('aBool'=>'N');
		$this->assertFalse ($inp->getValue ($from));
		
		$from = array ('aBool'=>'B');
		$this->assertFalse ($inp->getValue ($from));
		
		$from = array ('aBool'=>'');
		$this->assertFalse ($inp->getValue ($from));
	}
	
	function testIDInput () {
		$inp = new IDInput ('anID');
		$from = array ('anID'=>'7');
		$r = $inp->checkInput ($from);
		$this->assertFalse (isError ($r));
		
		$from = array ('anID'=>'0');
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('INVALID_ID'));
		
		$from = array ('anID'=>'-1');
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('INVALID_ID'));
		
		$from = array ('anID'=>'');
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('EMPTY_INPUT'));
	}
	
	function testMultipleInput () {
		$inp = new MultipleInput ('aDate', array ('year'=>'IntInput',
			'month'=>'IntInput','day'=>IntInput));
		$from = array ('aDateyear'=>'2006');
		$r = $inp->isGiven ($from);
		$this->assertFalse ($r);
		$this->assertSame (2006, $inp->getChildValue ($from, 'year'));
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('EMPTY_INPUT'));
		$from = array ('aDateyear'=>'2006', 'aDatemonth'=>'7');
		$r = $inp->isGiven ($from);
		$this->assertFalse ($r);
		$from = array ('aDateyear'=>'2006', 'aDatemonth'=>'7', 'aDateday'=>'25');
		$r = $inp->isGiven ($from);
		$this->assertTrue ($r);
		$r = $inp->checkInput ($from);
		$this->assertFalse (isError ($r));
		$r = $inp->getValue ($from);
		$this->assertTrue ($r->is ('MULTIPLE_INPUT_HANDLER_GET_VALUE_NYI'));
		$this->assertEquals ('aDate', $inp->getName ());
		
	}
	
	function testNewPasswordInput () {
		$inp = new PasswordNewInput ('newPassword');
		$from = array ('newPassword1'=>'bla');
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('EMPTY_INPUT'));
		
		$from = array ('newPassword1'=>'', 'newPassword2'=>'');
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('EMPTY_INPUT'));
		
		$from = array ('newPassword1'=>'a', 'newPassword2'=>'b');
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('PASSWORDS_NOT_EQUAL'));
		
		$from = array ('newPassword1'=>'a', 'newPassword2'=>'a');
		$r = $inp->checkInput ($from);
		$this->assertFalse (isError ($r));
		$this->assertEquals ('a', $inp->getValue ($from));		
	}
}

class actionManagerTest extends TestCase {

	function testMockup () {
		//$this->assertFalse (true, 'Not yet implemented');
	}
}

?>