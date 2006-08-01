<?php
	include_once ('test.class.php');
	initTester (true, 'MorgOS');
	include_once ('compatible.test.php');
	include_once ('config.test.php');
	runTests ();
	showAllResults ();
	deInitTester ();
?>