<?php
	include_once ('../compatible.php');
	addTestToRun ('compatible.php', 'file_get_contents', 'testFileGetContents', array ());
	addTestToRun ('compatible.php', 'array_search'     , 'testArraySearch'    , array ());
	addTestToRun ('compatible.php', 'scandir'          , 'testScanDir'        , array ());
	addTestToRun ('compatible.php', 'versionCompare'   , 'testVersionCompare' , array ());
	addTestToRun ('compatible.php', 'call_user_array'  , 'testCallUserArray'  , array ());
	
	function testFileGetContents () {
		$return = @file_get_contents ('notExistingFile.extension');
		testResult ('File not found returns not false.', $return !== false);
		
		$return = file_get_contents ('file.empty');
		$shouldReturn = NULL;
		testResult ('Empty file doesn\'t return NULL', $return != $shouldReturn);
		
		$return = file_get_contents ('file.txt');
		$shouldReturn =
			"This a fileNEWLINE".
			"and this is a new lineNEWLINE".
			"this file should become bigger than 1024 bytesNEWLINE";
		
		testResult ('File contents doesn\'t match, propably problems with NEWLINES.', $return != $shouldReturn);
	}
	
	function testArraySearch () {
		$array = array ();
		$array[7] = 'seven';
		$array['8'] = 'string';
		$array[1] = '1';
		$array[2] = 1;
		$array[0] = 'zero';
		
		$result = array_search ('seven', $array);
		testResult ('An array_search error', $result !== 7);
		
		$result = array_search ('string', $array);
		testResult ('An array_search error 2', $result !== 8); // for some reason it is converted to an int
		
		$result = array_search ('1', $array);
		testResult ('An array_search error 3', $result !== 1 and $result !== 2); // for some reason PHP 4 and PHP 5 gives something else here
		
		$result = array_search (1, $array, true);
		testResult ('A strict error', $result !== 2);
		
		$result = array_search ('zero', $array);
		testResult ('A zero error', $result !== 0);
		
		$result = array_search ('notfound', $array);
		testResult ('A notfound error', ($result !== false) and ($result !== NULL));
	}
	
	function testScanDir () {
		$result = scandir ('adir', 0);
		$shouldReturn = array ('.', '..', '.svn', 'a', 'b', 'd', 'e1', 'e10', 'e11', 'e2');
		testResult ('An error, probably in sorting', $shouldReturn !== $result);
		
		$result = scandir ('adir', 1);
		$shouldReturn = array ('e2', 'e11', 'e10', 'e1', 'd', 'b', 'a', '.svn', '..', '.');
		testResult ('An error, probably in inverse sorting', $shouldReturn !== $result);
		
		$result = scandir ('emptydir');
		testResult ('An empty dir doesn\t return array (., .., .svn)', $result !== array ('.', '..', '.svn'));
		
		$results = @scandir ('notexistingdir');
		testResult ('Dir not exists doesn\'t return false', $result === false);
		
		$results = @scandir ('notadir');
		testResult ('Not a dir doesn\'t return false', $result === false);
	}
	
	function testVersionCompare () {
		$result = versionCompare ('0.0.1', '0.0.2', ">");
		$shouldReturn = false;
		testResult ("A comparing error 1", $result !== $shouldReturn);
		
		$result = versionCompare ('0.0.1', '0.0.2', "<");
		$shouldReturn = true;
		testResult ("A comparing error 2", $result !== $shouldReturn);
		
		$result = versionCompare ('0.0.1', '0.0.2', "==");
		$shouldReturn = false;
		testResult ("A comparing error 3", $result !== $shouldReturn);
		
		$result = versionCompare ('0.0.1', '0.0.2', ">=");
		$shouldReturn = false;
		testResult ("A comparing error 4", $result !== $shouldReturn);
		
		$result = versionCompare ('0.0.1', '0.0.2', "<=");
		$shouldReturn = true;
		testResult ("A comparing error 5", $result !== $shouldReturn);
		
		$result = versionCompare ('1.0.1', '1.1', "<");
		$shouldReturn = true;
		testResult ("A comparing error 6", $result !== $shouldReturn);
		
		$result = versionCompare ('0.0.1', '0.0.*', "==");
		$shouldReturn = true;
		testResult ("A comparing error 7, probably in * comparision", $result !== $shouldReturn);
		
		$result = versionCompare ('0.0.1', '0.0.1', "<=");
		$shouldReturn = true;
		testResult ("A comparing error 8", $result !== $shouldReturn);
		
		$result = versionCompare ('1.0.1', '0.1.2', ">");
		$shouldReturn = true;
		testResult ("A comparing error 9", $result !== $shouldReturn);
		
		$result = versionCompare ('1.2.*', '1', "==");
		$shouldReturn = true;
		testResult ("A comparing error 10", $result !== $shouldReturn);
		
		$result = versionCompare ('1.2.*', '1', ">");
		$shouldReturn = false;
		testResult ("A comparing error 11", $result !== $shouldReturn);
		
		$result = versionCompare ('1.1', '1.0', "!=");
		$shouldReturn = true;
		testResult ("A comparing error 12", $result !== $shouldReturn);
	}
	
	class testClass {
		static function test ($param1, $param2) {
			global $firstParam, $secondParam;
			$firstParam = $param1;
			$secondParam = $param2;
			
			$param1++;
			$param2++;
		}
		
		function test2 ($param1, $param2) {
			global $firstParam, $secondParam;
			$firstParam = $param1;
			$secondParam = $param2;
			
			$param1++;
			$param2++;
			$this->param1 = $param1;
		}
	}
	
	function testFunction ($param1, $param2) {
		global $firstParam, $secondParam;
		$firstParam = $param1;
		$secondParam = $param2;
		
		$param1++;
		$param2++;
	}
	
	function testCallUserArray () {
		global $firstParam, $secondParam;
		call_user_func_array ('testFunction', array (1,2));
		testResult ("A call_user_func_array 1", $firstParam !== 1 and $firstParam !== 2);

		$test1 = 2;
		$test2 = 3;
		call_user_func_array ('testFunction', array (&$test1, &$test2));
		testResult ("A call_user_func_array 2, probably in references", $test1 !== 3 and $test2 !== 4);
		
		$test1 = 2;
		$test2 = 3;
		call_user_func_array (array ('testClass', 'test'), array (&$test1, &$test2));
		testResult ("A call_user_func_array 3, probably in objects", $test1 !== 3 and $test2 !== 4);
		
		$test1 = 2;
		$test2 = 3;
		$testClass = new testClass ();
		call_user_func_array (array (&$testClass, 'test2'), array (&$test1, &$test2));
		testResult ("A call_user_func_array 4, probably in object references", $test1 !== 3 and $test2 !== 4 and $testClass->param1 !== 3);
	}
?>