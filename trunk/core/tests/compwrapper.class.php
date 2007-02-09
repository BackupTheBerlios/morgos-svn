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
		function TestSuite () {
			$this->__construct ();
		}		
	}
	
	class TestCase extends PHPUnit_Framework_TestCase {
	}
	
	class TestResult extends PHPUnit_Framework_TestResult {
	}
} else {
	die ('Unsupported PHP version');
}


if (version_compare (PHP_VERSION, '5', '>=')) {
	function runTest ($class, $file, $p4) {
		$config = parse_ini_file ('options.ini');
		
		$cc = false;
		if ($config['phpUnitCC'] == true) {
			if (array_key_exists ('cc', $_GET)) {
				if ($_GET['cc'] == 'Y') {
					$cc = true;
				}
			}
		}
		if ($cc == true) {
			$config['phpUnitParameters'] .= ' --report ' . $config['phpUnitCCOutputPath'];
		}	
		
		$statement = $config['phpUnitPath'] . ' ' . 
			$config['phpUnitParameters'] . ' '.$class.' '.$file;
				
		chdir ('../..');
		
		ob_start ();
		system ($statement, $returnVar);
		$exec = ob_get_contents ();
		ob_end_clean ();
		
		if (! $returnVar) {
			if ($cc) {
				echo ('<a href="../../'.$config['phpUnitCCOutputPath'].'">Visit code coverage output</a> &nbsp; &nbsp; &nbsp;');
				echo ('<a href="./index.php?cc=Y">Rerun</a><br /><br />');
				echo ('<a href="./index.php">Rerun without code coverage</a><br /><br />');			
			} else {
				if ($config['phpUnitCC'] == true) {
					echo ('<a href="./index.php?cc=Y">Rerun with code coverage</a><br /><br />');
					echo ('<a href="./index.php">Rerun</a><br /><br />');
				}
			}
		}
		echo nl2br (htmlentities ($exec));
	}
} elseif (version_compare (PHP_VERSION, '4', '>=')) {
	function runTest ($p5a, $p5b, $file) {
		chdir ('../..');
		include_once ($file);
		$suite = new TestSuite ();
		loadSuite ($suite);
	
		require_once ('PHPUnit/GUI/HTML.php');
		$GUI = new PHPUnit_GUI_HTML ($suite->getAllTests ());
		$GUI->show ();
	}
}


?>