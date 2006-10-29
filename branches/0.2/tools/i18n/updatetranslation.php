<?php

if (!function_exists ('scandir')) {
	gettype ($_POST); // this is here only to trick Doxygen
    /**
	 * List files and directories inside the specified path
	 * @warning this is not fully compatible with the one defined in PHP 5 (missing context param)
	 * @warning sorting doesn't happen with natural sorting order, you need to do this manually
	 *
	 * @param $directory (string)
	 * @param $sortingOrder (int) 1 if descending, otherwise ascending
	 * @return (array | false)
    */
	function scandir ($directory, $sortingOrder = 0) {
		if (! file_exists ($directory)) {
			return false;
		}
		if (! is_dir ($directory)) {
			return false;
		}

		$handler = opendir ($directory);
		if ($handler === false) {
			return false;
		} else {
			$files = array ();
			while (false !== ($file = readdir ($handler))) {
				$files[] = $file;
			}
            
			sort ($files, SORT_STRING);
    
			if ($sortingOrder == 1) {
				$files = array_reverse ($files);
			}
            
			return $files;
		}
	}
}

function getFileExtension ($fileName) {
	$parts = explode ('.', $fileName);
	return $parts[count ($parts)-1];
}


function getFiles ($i, $pref) {
	$output = array ();

	foreach ($i as $f) {
		$f = $pref.'/'.$f;
		if (is_dir ($f)) {
			$base = basename ($f);
			if ($base{0} !== '.') {
				if (($base !== 'tests') and ($base !== 'smarty') and ($base !== 'tinymce')) {
					$output = array_merge ($output, getFiles (scandir ($f), $f));
				}
			}
		} else {
			$output[] = $f;
		}
	}
	return $output;
}


if (count ($argv) >= 4) {
	$langCode = $argv[1];
	$outputDir = $argv[2];
	$outputFile = $outputDir.'/'.$langCode.'.trans.php';
	
	if (file_exists ($outputFile)) {
		include ($outputFile);	
		$currentErrors = $errorStrings;
		$currentStrings = $strings;
	} else {
		$currentStrings = array ();
		$currentErrors = array ();
	}
	
	// create an array of all inputfiles
	$input_files_and_dirs = array_slice ($argv, 3);
	$input_files = getFiles ($input_files_and_dirs, '.');
	
	foreach ($input_files as $file) {
		$input = file_get_contents ($file);
		//$input = 'translate ("")';
		switch (getFileExtension ($file)) {
			case 'php':
				preg_match_all ("/translate \(['|\"]([\w|\s|.|(|)|%|:|'|,]*)['|\"]\)/", $input, $matches);
				foreach ($matches[0] as $k=>$match) {
					$string = $matches[1][$k];
					if (! array_key_exists ($string, $currentStrings)) {
						$currentStrings[$string] = '';
					}
				}				
				
				break;
			case 'tpl':
				preg_match_all ("/\{t s=[\"|']([\w|\s|.|(|)|%|:|>|<]*)[\"|']\}/", $input, $matches);
				foreach ($matches[0] as $k=>$match) {
					$string = $matches[1][$k];
					if (! array_key_exists ($string, $currentStrings)) {
						$currentStrings[$string] = '';
					}
				}
				break;
		}
	}
	
	$h = fopen ($outputFile, "w");
	
	$output = "<?php \n";
	foreach ($currentStrings as $k=>$string) {
		$k = str_replace ("'", "\'", $k);
		$output .= '$strings[\''.$k.'\'] = \''.$string.'\';'."\n";
	}
	$output .= "?>";
	fwrite ($h, $output);
	fclose ($h);
	
} else {
	echo "HOWTO Use:\n";
	echo "php updatetranslation.php langCode outputdir input...\n";
	echo "input is automatically recursive\n";
}


?>