<?php
	include_once ('test.class.php');
	initTester (true, 'MorgOS');
	include_once ('compatible.test.php');
	runTests ();
	showAllResults ();
	deInitTester ();
?>