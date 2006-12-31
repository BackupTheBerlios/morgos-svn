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

class InputTest extends TestCase {
	function testStringInput () {
		$inp = new StringInput ('aString');
		$from = array ();
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('EMPTY_INPUT'));
		$r = $inp->getValue ($from);
		$this->assertTrue ($r->is ('EMPTY_INPUT'));
		$this->assertEquals ('aString', $inp->getName ());
		$from = array ('aString'=>'');
		$r = $inp->checkInput ($from);
		$this->assertTrue ($r->is ('EMPTY_INPUT'));
		$from = array ('aString'=>'testInput');
		$this->assertTrue ($inp->isGiven ($from));
		$r = $inp->getValue ($from);
		$this->assertEquals ('testInput', $r);
		
		$_POST = array ('aString'=>'aValue');
		$this->assertEquals ('aValue', $inp->getValue ('POST'));
		$_GET = array ('aString'=>'aValue');
		$this->assertEquals ('aValue', $inp->getValue ('GET'));
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
			'month'=>'IntInput','day'=>'IntInput'));
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
		$this->assertTrue ($r);
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

class ActionManagerTest extends TestCase {
	var $_actionM;
	var $_anAction;
	var $_aPageAction;
	var $_autoTriggerAction;
	var $_lastExecuted = 'none';
	var $_lastParams = array ();

	function setUp () {
		global $actionM;
		if (! $actionM) {
			$actionM = new ActionManager ();	
		}
		$this->_actionM = $actionM;
		$this->_anAction = new Action ('noPageAction', 'GET', 
			array ($this, 'executeNoPageAction'), array (new StringInput ('reqOp')),
			array (new IDInput ('nonReqOp')));
			
		$this->_aPageAction = new Action ('pageAction', 'GET', 
			array ($this, 'executePageAction'), array (new StringInput ('reqOp')),
			array (new IDInput ('nonReqOp')), 'MorgOS_PageName', false);
		$this->_autoTriggerAction = new Action ('autoTriggerAction', 'POST', 
			array ($this, 'executeAutoTriggerAction'), array (new StringInput ('reqOp')),
			array (new IDInput ('nonReqOp')), 'MorgOS_PageName', true);
	}

	function testBareAction () {
		$this->assertEquals ('noPageAction', $this->_anAction->getName ());
		$this->assertEquals ('pageAction', $this->_aPageAction->getName ());
		$this->assertEquals ('autoTriggerAction', $this->_autoTriggerAction->getName ());
		$this->assertEquals (null, $this->_anAction->getPageName ());
		$this->assertEquals ('MorgOS_PageName', $this->_aPageAction->getPageName ());
		$this->assertEquals ('MorgOS_PageName', 
			$this->_autoTriggerAction->getPageName ());
		$this->assertFalse ($this->_anAction->autoTrigger ());
		$this->assertFalse ($this->_aPageAction->autoTrigger ());
		$this->assertTrue ($this->_autoTriggerAction->autoTrigger ());
	}
	
	function testBareActionExecute () {
		$this->assertEquals ('none', $this->_lastExecuted);
		$this->assertEquals (array (), $this->_lastParams);
		$_GET = array ('reqOp'=>'aString', 'nonReqOp'=>'');
		$this->_anAction->execute ();
		$this->assertEquals ('anAction', $this->_lastExecuted);
		$this->assertEquals ($_GET, $this->_lastParams);
		$this->_aPageAction->execute ();
		$this->assertEquals ('aPageAction', $this->_lastExecuted);
		$this->assertEquals ($_GET, $this->_lastParams);
		$_POST = array ('reqOp'=>'aString', 'nonReqOp'=>'');
		$this->_autoTriggerAction->execute ();
		$this->assertEquals ('autoTriggerAction', $this->_lastExecuted);
		$this->assertEquals ($_POST, $this->_lastParams);
	}
	
	function executeNoPageAction ($reqOp, $nonReqOp) {
		$this->_lastExecuted = 'anAction';
		$this->_lastParams = array ('reqOp'=>$reqOp, 'nonReqOp'=>$nonReqOp);
	}
	
	function executePageAction ($reqOp, $nonReqOp) {
		$this->_lastExecuted = 'aPageAction';
		$this->_lastParams = array ('reqOp'=>$reqOp, 'nonReqOp'=>$nonReqOp);
	}
	
	function executeAutoTriggerAction ($reqOp, $nonReqOp) {
		$this->_lastExecuted = 'autoTriggerAction';
		$this->_lastParams = array ('reqOp'=>$reqOp, 'nonReqOp'=>$nonReqOp);
	}
}

?>