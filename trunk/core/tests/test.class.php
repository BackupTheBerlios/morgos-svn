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
/** \file test.class.php
 * Functions that handles errorreporting to see if everything is working well.
 *
 * \author Nathan Samson
*/

/** \fn testErrorHandler ($errNo, $errStr, $errFile = NULL, $errLine = 0, $errContext = NULL)
 * This adds each time an error occurs it to the inside log.
 * When all tests are done (or before) this log is checked and all errors  are showed.
*/
function testErrorHandler ($errNo, $errStr, $errFile = NULL, $errLine = 0, $errContext = NULL) {
	global $testerArray, $isTesterInited, $runningTest;
	if (error_reporting () != 0) {
		if ($isTesterInited == true) {
			if ($runningTest != NULL) {
				if ($testerArray['debug'] == true) {
					$testerArray['log'][$runningTest[0]][$runningTest[1]] = array ($errNo, $errStr, $errFile, $errLine);
				} else {
					$testerArray['log'][$runningTest[0]][$runningTest[1]] = array ($errNo, $errStr);
				}
			} else {
				$testerArray['globalerrors'][] = array ($errNo, $errStr, $errFile, $errLine);
			}
		} else {
			dieFromError ('Tester is not inited, init the tester before running.');
			die ();
		}
	}
}
	
/* \fn initTester ($doDebug = true)
 * inits the tester.
 * \warning When this function is called before all information will be lost FOREVER.
*/
function initTester ($doDebug = true, $name) {
	global $testerArray, $isTesterInited, $runningTest;
	$testerArray = array ();
	$testerArray['log'] = array ();
	$testerArray['name'] = $name;
	$testerArray['debug'] = $doDebug;
	$testerArray['dotests'] = array ();
	$testerArray['runnedtests'] = array ();
	$runningTest = NULL;
	set_error_handler ('testErrorHandler');
	$isTesterInited =true;
}

/* \fn deInitTester ()
 * deinits the tester.
*/
function deInitTester () {
	global $testerArray, $isTesterInited;
	$isTesterInited = false;
	unset ($testerArray);
	restore_error_handler ();
}

/* \fn addTestToRun ($group, $name, $functionCaller, $functionParams)
*/
function addTestToRun ($group, $name, $functionCaller, $functionParams) {
	global $testerArray;
	$testerArray['log'][$group][$name] = array ();
	$testerArray['dotests'][$group][] = array ('name' => $name, 'func' => $functionCaller, 'params' => $functionParams);
}

function runTests () {
	global $testerArray;
	foreach ($testerArray['dotests'] as $groupName => $groupArray) {
		foreach ($groupArray as $testToRun) {
			global $runningTest;
			$runningTest = array ($groupName, $testToRun['name']);
			$testerArray['runnedtests'][$groupName][$testToRun['name']][0] = 'succeed';
			call_user_func_array ($testToRun['func'], $testToRun['params']);
			$runningTest = NULL;
		}
	}
}

function errorInTest ($error) {
	global $testerArray, $runningTest;
	$testerArray['runnedtests'][$runningTest[0]][$runningTest[1]][0] = 'failed';
	$testerArray['runnedtests'][$runningTest[0]][$runningTest[1]][] = $error;
}

function failedTest () {
}

function showAllResults () {
	global $runningTest, $testerArray;
	echo "<html>
		<head>
			<title>$testerArray[name] automated tester</title>
		</head>
		<body>
		<h1>Welcome by the $testerArray[name] automated tester.</h1><p>Testing results:</p>";
	foreach ($testerArray['runnedtests'] as $groupName => $groupArray) {
		echo "<h2>$groupName</h2>";
		foreach ($groupArray as $name => $testToRun) {
			$result = $testToRun[0];
			if ($result == 'failed') {
				$result = '<span class="failed">failed</span>';
				$result .= "<br />Error was: '$testToRun[1]'";
			} else {
				$result = '<span class="failed">successfull</span>';
			}
			echo "<h3>$name</h3>
				<p>Result: $result
				</p>";
			$expectedErrors = NULL;
			foreach ($testerArray['log'][$groupName][$name] as $error) {
				$expectedErrors .= $error;
			}
			if ($expectedErrors != NULL) {
				echo "<h4>Expected errors that were thrown during execution.</h4>
				<p>$errors</p>";
			}
			
			$unexpectedErrors = NULL;
			foreach ($testerArray['log'][$groupName][$name] as $error) {
				$unexpectedErrors .= $error;
			}
			if ($unexpectedErrors != NULL) {
				echo "<h4>Unexpected errors that were thrown during execution.</h4>
				<p>$unexpectedErrors</p>";
			}
		}
	}
	
	$errors = NULL;
	foreach ($testerArray['globalerrors'] as $error) {
		$errors .= $error[2] . '@' .$error[3] . ': ' . $error[1] . '<br />';
	}
	if ($errors != NULL) {
		echo "<p>Other errors during execution: <br />$errors</p>";
	}
	echo '</body></html>';
}

/* \fn dieFromError ()
 * Shows a nice page with what went wrong. Call this only for FATAL errors of the tester
 * and not when some tests can not be runned.
*/
function dieFromError () {
}