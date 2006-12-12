<?php
require_once ('core/tests/base.php');

class MorgOSLoader {
	public static function main () {
		PHPUnit_TextUI_TestRunner::run (self::suite ());
	}

	public static function suite () {
		$suite = new PHPUnit_Framework_TestSuite ('MorgOS Test suite');
		loadSuite ($suite);
		return $suite;
	}
}


?>