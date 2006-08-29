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
/** \file config.class.test.php
 * File that take care of testing config class.
 *
 * @since 0.2
 * @author Nathan Samson
*/

include_once ('core/varia.functions.php');
include_once ('interface/eventmanager.class.php');

class testObject {
	var $testValue;

	function nonStaticFunction () {
		return $this->testValue;
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
	return ($t < $ob->testValue);
}

class eventManagerTest extends TestCase {
	var $eventManager;
	var $runEvent;
	var $onRunCallback;	
	
	function setUp () {
		$this->eventManager = new eventManager ();
		$this->runEvent = new event ('run');
		$this->onRunCallback = new callback ('onRun', 'onRun');
	}
	
	function testAddEvent () {
		$this->eventManager->addEvent ($this->runEvent);
		$this->assertTrue ($this->eventManager->existsEvent ('run'));
		$r = $this->eventManager->addEvent ($this->runEvent);
		$this->assertEquals ($r, "ERROR_EVENTMANAGER_EVENT_EXISTS run");
	}
	
	function testRemoveEvent () {
		$r = $this->eventManager->removeEvent ($this->runEvent->getName ());
		$this->assertEquals ($r, "ERROR_EVENTMANAGER_EVENT_DOESNT_EXISTS run");
		
		$this->eventManager->addEvent ($this->runEvent);
		$r = $this->eventManager->removeEvent ($this->runEvent->getName ());
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertFalse ($this->eventManager->existsEvent ('run'));
	}
	
	function testAddCallback () {
		$this->runEvent->addCallback ($this->onRunCallback);
		$this->assertTrue ($this->runEvent->existsCallback ('onRun'), 'Not added');
		$r = $this->runEvent->addCallback ($this->onRunCallback);
		$this->assertEquals ($r, "ERROR_EVENT_CALLBACK_EXISTS onRun", 'Wrong error returned');
	}
	
	function testRemoveCallback () {
		$r = $this->runEvent->removeCallback ($this->onRunCallback->getName ());
		$this->assertEquals ($r, "ERROR_EVENT_CALLBACK_DOESNT_EXISTS onRun", 'Wrong error');
		
		$this->runEvent->addCallback ($this->onRunCallback);
		$r = $this->runEvent->removeCallback ($this->onRunCallback->getName ());
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertFalse ($this->runEvent->existsCallback ('onRun'), 'Not removed');
	}
	
	function testTriggerWithoutParams () {
		$this->eventManager->addEvent ($this->runEvent);
		$this->runEvent->addCallback ($this->onRunCallback);
		$a = $this->eventManager->triggerEvent ('run');
		$this->assertEquals (array ('onRun'=> false), $a);
	}
	
	function testTriggerMultiple () {
		$this->eventManager->addEvent ($this->runEvent);
		$this->runEvent->addCallback ($this->onRunCallback);
		$this->onRunCallback2 = new callback ('onRun2', 'onRun2');
		$this->runEvent->addCallback ($this->onRunCallback2);
		$a = $this->eventManager->triggerEvent ('run');
		$this->assertEquals (array ('onRun'=> false, 'onRun2'=>true), $a);
	}
	
	function testTriggerWithParams () {
		$this->eventManager->addEvent ($this->runEvent);
		$this->runEvent->addCallback ($this->onRunCallback);
		$test1 = 0;
		$ob = new testObject ();
		$ob->testValue = -1;
		$this->onRunWithParams = new callback ('onRunWithParams', 'onRunWithParams',  array (&$test1, $ob));
		$this->runEvent->addCallback ($this->onRunWithParams);
		$test1 = 3;
		$ob->testValue = 5;
		$a = $this->eventManager->triggerEvent ('run');
		$this->assertEquals (array ('onRun'=> false, 'onRunWithParams'=>true), $a);
	}
	
	function testTriggerWithStaticObject () {
		$this->eventManager->addEvent ($this->runEvent);
		$this->onRunWithStaticObject = new callback ('onRunWithStaticObject', array ('testObject', 'staticFunction'));
		$this->runEvent->addCallback ($this->onRunWithStaticObject);
		$a = $this->eventManager->triggerEvent ('run');
		$this->assertEquals (array ('onRunWithStaticObject'=> 'static'), $a);
	}
	
	function testTriggerWithNonStaticObject () {
		$this->eventManager->addEvent ($this->runEvent);
		$ob = new testObject ();
		$ob->testValue = 0;
		$this->onRunWithNonStaticObject = new callback ('onRunWithNonStaticObject', array ($ob, 'nonStaticFunction'));
		$this->runEvent->addCallback ($this->onRunWithNonStaticObject);
		$ob->testValue = 7;
		$a = $this->eventManager->triggerEvent ('run');
		$this->assertEquals (array ('onRunWithNonStaticObject'=> 7), $a);
	}
	
	function testTriggerRemoteWithParamsRemoteAndInternal () {
		$tEvent = new Event ('someEvent', array ('param1', 'param2'));
		$this->eventManager->addEvent ($tEvent);
		$tCallback = new callback ('someCallback', array ($this, 'remoteEvent'), array ('param1', &$a, 'param2', &$b));
		$a = 3;
		$b = 4;
		$tEvent->addCallback ($tCallback);
		$a = $this->remoteTrigger ();
		$this->assertEquals (array ('someCallback'=> true), $a);
	}
	
	function remoteTrigger () {
		$a = 7;
		$b = 8;
		return $this->eventManager->triggerEvent ('someEvent', array ('1', '2'));
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