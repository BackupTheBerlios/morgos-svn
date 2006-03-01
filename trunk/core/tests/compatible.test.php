<?php


	include_once ('../compatible.php');
	addTestToRun ('compatible.php', 'file_get_contents', 'testFileGetContents', array ());
	addTestToRun ('compatible.php', 'array_search'     , 'testArraySearch'    , array ());
	addTestToRun ('compatible.php', 'scan_dir'         , 'testScanDir'        , array ());
	addTestToRun ('compatible.php', 'versionCompare'   , 'testVersionCompare' , array ());
	addTestToRun ('compatible.php', 'call_user_array'  , 'testCallUserArray'  , array ());
	
	function testFileGetContents () {
		setExpectingError ('file_get_contents(notExistingFile.extension) [<a href=\'function.file-get-contents\'>function.file-get-contents</a>]: failed to open stream: No such file or directory');
		$return = file_get_contents ('notExistingFile.extension');
		cleanExpectedErrors ();
		testResult ('File not found returns not false.', $return !== false);
		
		$return = file_get_contents ('file.empty');
		$shouldReturn = NULL;
		testResult ('Empty file doesn\'t return NULL', $return != $shouldReturn);
		
		$return = file_get_contents ('file.txt');
		$shouldReturn =
			"This a fileNEWLINE".
			"and this is a new lineNEWLINE".
			"this file should become bigger than 1024 bytesNEWLINE";
		
		testResult ('File contents doesn\'t match.', $return != $shouldReturn);
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
		testResult ('An array_search error 2', $result !== 8);
		
		$result = array_search ('1', $array);
		testResult ('An array_search error 3', $result !== 1);
		
		$result = array_search (1, $array, true);
		testResult ('A strict error', $result !== 2);
		
		$result = array_search ('zero', $array);
		testResult ('A zero error', $result !== 0);
		
		$result = array_search ('notfound', $array);
		testResult ('A notfound error', ($result !== false) and ($result !== NULL));
	}
	
	function testScanDir () {
	}
	
	function testVersionCompare () {
	}
	
	function testCallUserArray () {
	}
?>