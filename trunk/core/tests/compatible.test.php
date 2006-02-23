<?php
	include_once ('../compatible.php');

	addTestToRun ('compatible.php', 'file_get_contents', 'testFileGetContents', array ());
	
	function testFileGetContents () {
		$return = file_get_contents ('notExistingFile.extension');
		if ($return !== false) {
			errorInTest ('File not found returns not false.');
		}
		
		$return = file_get_contents ('file.empty');
		$shouldReturn = NULL;
		if ($return != $shouldReturn) {
			errorInTest ('Empty file doesn\'t return NULL');
		}
		
		$return = file_get_contents ('file.txt');
		$shouldReturn =
			"This a fileNEWLINE".
			"and this is a new lineNEWLINE".
			"this file should become bigger than 1024 bytesNEWLINE";

		if ($return != $shouldReturn) {
			errorInTest ('File contents doesn\'t match.');
		}
		
		
	}
?>