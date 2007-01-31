<?php

require_once 'PHPUnit2/Framework/TestListener.php';
class SimpleTestListener implements PHPUnit2_Framework_TestListener {
	public function addError(PHPUnit2_Framework_Test $test, Exception $e) {
		$test->failed = true;
		echo '<td class="error">Fout ('.htmlentities ($e->getMessage ()) /*.' '. $e->getFile () .' '.$e->getLine ()*/.')</td>';
	}
 
	public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e) {
		$test->failed = true;
		echo '<td class="failure">Gefaald ('.htmlentities ($e->getMessage ()) /*.' '. $e->getFile ().' '.$e->getLine ()*/.')</td>';
  	}
 
	public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e) {
		$test->failed = true;
		echo '<td class="inc">Incomplete ('.htmlentities ($e->getMessage ()) /*.' '. $e->getFile .' '.$e->getLine ()*/.')</td>';
		
	}
 
	public function startTest(PHPUnit2_Framework_Test $test) {
		echo '<tr>';
		$test->failed = false;
		echo '<td>'.$test->getName ().'</td>';
	}
 
	public function endTest(PHPUnit2_Framework_Test $test) {
		if (! $test->failed) {
			echo '<td class="ok">Geslaagd</td>';
		}
		echo '</tr>';
	}
 
	public function startTestSuite(PHPUnit2_Framework_TestSuite $suite) {
		echo '<h1>'.$suite->getName ().'</h1>';
		echo '<table>';
	}
 
	public function endTestSuite(PHPUnit2_Framework_TestSuite $suite) {
		echo '</table>';
	}
	
	private function printBt () {

	}
}
?>