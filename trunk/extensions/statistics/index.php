<?php
	if (! class_exists ('StatisticsExtension')) {
	class StatisticsExtension {
		function StatisticsExtension ($UI) {
			$this->__construct ($UI);
		}
	
		function __construct ($UI) {
			$this->UI = $UI;
			$UI->pages->prependTextToPageContent ('Hello StatisticsExtension. <br />');
			$UI->pages->appendTextToPageContent ('<br />See You later alligator.');
			$params = array ();
			$UI->signalMan->connectSignal ('loadPage', array ($this, 'onLoadPage'), $params);
		}
		
		function onLoadPage ($name, $language) {
		}
	}
	}
	$UI = $arrayOfObjects['UI'];
	return new StatisticsExtension ($UI);
?>
