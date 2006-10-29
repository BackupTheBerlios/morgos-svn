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
/** \file interface/tests/index.php
 * Suite for the interface tests
 *
 * @since 0.2
 * @author Nathan Samson
*/

chdir ('../..');
include_once ('core/tests/base.php');

class MorgOSInterfaceSuite extends TestSuite {
	function MorgOSInterfaceSuite () {
		$this->setName ('MorgOS interface tester');
	}

}

if ($php == "4") {
	include_once ('interface/tests/eventmanager.class.test.php');
	include_once ('interface/tests/actionmanager.class.test.php');
	include_once ('interface/tests/pluginmanager.class.test.php');
	require_once ('PHPUnit/GUI/HTML.php');
	$eventsuite = new TestSuite ('eventManagerTest');
	$pluginsuite = new TestSuite ('pluginManagerTest');
	$actionsuite = new TestSuite ('actionManagerTest');
	$GUI = new PHPUnit_GUI_HTML (array ($eventsuite, $pluginsuite, $actionsuite));
	$GUI->show ();
} elseif ($php == "5") {
	$suite = new MorgOSInterfaceSuite ();
	//$eventTest = new PHPUnit2_Framework_TestSuite('eventManagerTest');
	$suite->addTestFile ('interface/tests/eventmanager.class.test.php');
	$suite->addTestFile ('interface/tests/actionmanager.class.test.php');
	$suite->addTestFile ('interface/tests/pluginmanager.class.test.php');
	$result = new TestResult;
	$result->addListener(new SimpleTestListener);
	$suite->run ($result);
	$suite = null;
}
?>
