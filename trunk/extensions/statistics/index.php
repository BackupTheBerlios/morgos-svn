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
			$UI->signalMan->connectSignal ('loadPage', array ($this, 'onLoadPage'));
		}
		
		function onLoadPage ($name, $language) {
			// fill this in.
		}
	}
	}
	$UI = $arrayOfObjects['UI'];
	return new StatisticsExtension ($UI);
?>
