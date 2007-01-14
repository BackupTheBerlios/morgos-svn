<?php
include_once ('core/tests/compwrapper.class.php');

function loadSuite (&$suite) {
	$suite->addTestFile ('interface/tests/eventmanager.class.test.php');
	$suite->addTestFile ('interface/tests/pluginmanager.class.test.php');
	$suite->addTestFile ('interface/tests/actionmanager.class.test.php');
	$suite->addTestFile ('interface/tests/pluginapi.class.test.php');
	$suite->addTestFile ('interface/tests/skinmanager.class.test.php');
	$suite->addTestFile ('interface/tests/extendedsmarty.class.test.php');
}

?>