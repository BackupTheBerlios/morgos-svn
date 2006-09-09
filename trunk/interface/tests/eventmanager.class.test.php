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
/** \file eventmanager.class.test.php
 * File that take care of testing eventmanager class.
 *
 * @since 0.2
 * @author Nathan Samson
*/

include_once ('core/varia.functions.php');
include_once ('interface/eventmanager.class.php');

class testObject {
	var $_testValue;

	function nonStaticFunction () {
		return $this->_testValue;
	}

	function staticFunction () {
		return 'static';
	}
}

function onRun () {
	return false;
}

function onRun2 () {
	return true;
}

function onRunWithParams ($t, $ob) {
	return ($t < $ob->_testValue);
}

class eventManagerTest extends TestCase {
	var $_eventManager;
	var $_runEvent;
	var $_onRunCallback;	
	
	function setUp () {
		$this->_eventManager = new eventManager ();
		$this->_runEvent = new event ('run');
		$this->_onRunCallback = new callback ('onRun', 'onRun');
	}
	
	function testAddEvent () {
		$this->_eventManager->addEvent ($this->_runEvent);
		$this->assertTrue ($this->_eventManager->existsEvent ('run'));
		$r = $this->_eventManager->addEvent ($this->_runEvent);
		$this->assertEquals (new Error ('EVENTMANAGER_EVENT_EXISTS', 'run'), $r);
	}
	
	function testRemoveEvent () {
		$r = $this->_eventManager->removeEvent ($this->_runEvent->getName ());
		$this->assertEquals (new Error ('EVENTMANAGER_EVENT_DOESNT_EXISTS', 'run'), $r);
		
		$this->_eventManager->addEvent ($this->_runEvent);
		$r = $this->_eventManager->removeEvent ($this->_runEvent->getName ());
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertFalse ($this->_eventManager->existsEvent ('run'));
	}
	
	function testAddCallback () {
		$this->_eventManager->addEvent ($this->_runEvent);
		$r = $this->_eventManager->subscribeToEvent ('run', $this->_onRunCallback);
		$this->assertFalse (isError ($r));
		$runEvent = $this->_eventManager->getEvent ('run');
		$this->assertTrue ($runEvent->existsCallback ($this->_onRunCallback->getName ()) , 'Not added');
		$r = $this->_eventManager->subscribeToEvent ('run', $this->_onRunCallback);
		$this->assertEquals (new Error ('EVENT_CALLBACK_EXISTS', 'onRun'), $r, 'Wrong error returned');
	}
	
	function testRemoveCallback () {
		$this->_eventManager->addEvent ($this->_runEvent);
		$r = $this->_eventManager->unSubscribeFromEvent ('run', $this->_onRunCallback);
		$this->assertEquals (new Error ('EVENT_CALLBACK_DOESNT_EXISTS', 'onRun'), $r, 'Wrong error');
		
		$r = $this->_eventManager->subscribeToEvent ('run', $this->_onRunCallback);
		$r = $this->_eventManager->unSubscribeFromEvent ('run', $this->_onRunCallback);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$runEvent = $this->_eventManager->getEvent ('run');
		$this->assertFalse ($runEvent->existsCallback ('onRun'), 'Not removed');
	}
	
	function testTriggerWithoutParams () {
		$this->_eventManager->addEvent ($this->_runEvent);
		$this->_eventManager->subscribeToEvent ('run', $this->_onRunCallback);
		$a = $this->_eventManager->triggerEvent ('run');
		$this->assertEquals (array ('onRun'=> false), $a);
	}
	
	function testTriggerMultiple () {
		$this->_eventManager->addEvent ($this->_runEvent);
		$this->_eventManager->subscribeToEvent ('run', $this->_onRunCallback);
		$this->_onRunCallback2 = new callback ('onRun2', 'onRun2');
		$this->_eventManager->subscribeToEvent ('run', $this->_onRunCallback2);
		$a = $this->_eventManager->triggerEvent ('run');
		$this->assertEquals (array ('onRun'=> false, 'onRun2'=>true), $a);
	}
	
	function testTriggerWithParams () {
		$this->_eventManager->addEvent ($this->_runEvent);
		$this->_eventManager->subscribeToEvent ('run', $this->_onRunCallback);
		$test1 = 0;
		$ob = new testObject ();
		$ob->_testValue = -1;
		$this->_onRunWithParams = new callback ('onRunWithParams', 'onRunWithParams',  array (&$test1, &$ob));
		$this->_eventManager->subscribeToEvent ('run', $this->_onRunWithParams);
		$test1 = 3;
		$ob->_testValue = 5;
		$a = $this->_eventManager->triggerEvent ('run');
		$this->assertEquals (array ('onRun'=> false, 'onRunWithParams'=>true), $a);
	}
	
	function testTriggerWithStaticObject () {
		$this->_eventManager->addEvent ($this->_runEvent);
		$this->_onRunWithStaticObject = new callback ('onRunWithStaticObject', array ('testObject', 'staticFunction'));
		$this->_eventManager->subscribeToEvent ('run', $this->_onRunWithStaticObject);
		$a = $this->_eventManager->triggerEvent ('run');
		$this->assertEquals (array ('onRunWithStaticObject'=> 'static'), $a);
	}
	
	function testTriggerWithNonStaticObject () {
		$this->_eventManager->addEvent ($this->_runEvent);
		$ob = new testObject ();
		$ob->_testValue = 0;
		$this->_onRunWithNonStaticObject = new callback ('onRunWithNonStaticObject', array (&$ob, 'nonStaticFunction'));
		$this->_eventManager->subscribeToEvent ('run', $this->_onRunWithNonStaticObject);
		$ob->_testValue = 7;
		$a = $this->_eventManager->triggerEvent ('run');
		$this->assertEquals (array ('onRunWithNonStaticObject'=> 7), $a);
	}
	
	function testTriggerRemoteWithParamsRemoteAndInternal () {
		$tEvent = new Event ('someEvent', array ('param1', 'param2'));
		$this->_eventManager->addEvent ($tEvent);
		$tCallback = new callback ('someCallback', array ($this, 'remoteEvent'), array ('param1', &$a, 'param2', &$b));
		$a = 3;
		$b = 4;
		$this->_eventManager->subscribeToEvent ('someEvent', $tCallback);
		$a = $this->remoteTrigger ();
		$this->assertEquals (array ('someCallback'=> true), $a);
	}
	
	function remoteTrigger () {
		$a = 7;
		$b = 8;
		return $this->_eventManager->triggerEvent ('someEvent', array ('1', '2'));
	}
	
	function remoteEvent ($intParam1, $a, $intParam2, $b) {
		if (($intParam1 == '1') and ($intParam2 == '2') and ($a ==3) and ($b == 4)) {
			return true;
		} else {
			return false;
		}
	}
}
?>