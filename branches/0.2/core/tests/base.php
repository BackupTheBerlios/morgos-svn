<?php
if (version_compare (PHP_VERSION, '5', '<=')) {
	$php = "4";
	require_once 'PHPUnit/TestSuite.php';
	//require_once 'PHPUnit/Util/TestDox/ResultPrinter/HTML.php';
	
	class TestSuite extends PHPUnit_TestSuite  {
	}
	
	class TestCase extends PHPUnit_TestCase {
	}
	
	class TestResult extends PHPUnit_TestResult {
	}
} elseif (version_compare (PHP_VERSION, '5', '>=')) {
	$php = "5";
	require_once 'PHPUnit2/Framework/TestSuite.php';
	require_once 'PHPUnit2/Util/TestDox/ResultPrinter/HTML.php';
	
	class TestSuite extends PHPUnit2_Framework_TestSuite  {
	}
	
	class TestCase extends PHPUnit2_Framework_TestCase {
	}
	
	class TestResult extends PHPUnit2_Framework_TestResult {
	}
} else {
	die ('Unsupported PHP version');
}

if ($php == "5") {
	require_once 'core/tests/testoutput.php';
}
?>