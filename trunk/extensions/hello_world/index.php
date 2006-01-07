<?php
	if (! class_exists ('HelloWorldExtension')) {
	class HelloWorldExtension {
		function HelloWorldExtension ($UI) {
			$this->_construct ($UI);
		}
	
		function _construct ($UI) {
			$this->UI = $UI;
			$UI->pages->prependTextToPageContent ('Hello World. <br />');
			$UI->pages->appendTextToPageContent ('<br />See You later alligator.');
		}
	}
	}
	$UI = $arrayOfObjects['UI'];
	return new HelloWorldExtension ($UI);
?>
