<?php
require_once ('interface/tests/base.php');

class MorgOSInterface {
	public static function main () {
		PHPUnit_TextUI_TestRunner::run (self::suite ());
	}

	public static function suite () {
		$suite = new PHPUnit_Framework_TestSuite ('MorgOS Interface Test suite');
		loadSuite ($suite);
		return $suite;
	}
}


?>