<?php
if (version_compare (PHP_VERSION, '5', '<=')) {
	$php = "4";
	require_once 'PHPUnit/TestSuite.php';
	//require_once 'PHPUnit/Util/TestDox/ResultPrinter/HTML.php';
	
	class TestSuite extends PHPUnit_TestSuite  {
		var $tests = array ();
	
		function getAllTests () {
			return $this->tests;
		}
	
		function addTestFile ($file) {
			$currentClasses = get_declared_classes ();
			include_once ($file);
			$newCurrentClasses = get_declared_classes (); 
		
			$diff = array_diff ($newCurrentClasses, $currentClasses);
			foreach ($diff as $newClass) {
				if (get_parent_class ($newClass) == 'testcase'
						|| get_parent_class ($newClass) == 'dbdrivergenerictest'){
					$class = new TestSuite ($newClass);
					$this->tests[] = $class;
				} 
			}
		}
	}
	
	class TestCase extends PHPUnit_TestCase {
		function __construct () {
			parent::PHPUnit_TestCase ();
		}
		
		function assertSame ($a, $b) {
			$this->assertTrue ($a === $b);
		}
	}
	
	class TestResult extends PHPUnit_TestResult {
	}
} elseif (version_compare (PHP_VERSION, '5', '>=')) {
	$php = "5";
	require_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';
	
	class TestSuite extends PHPUnit_Framework_TestSuite  {
	}
	
	class TestCase extends PHPUnit_Framework_TestCase {
	}
	
	class TestResult extends PHPUnit_Framework_TestResult {
	}
} else {
	die ('Unsupported PHP version');
}
?>