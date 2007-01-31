<?php

if (!function_exists ('scandir')) {
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
				if (($base !== 'tests') and ($base !== 'smarty') 
					and ($base !== 'tinymce') and ($base !== 'smarty-plugins')) {
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
	//$input_files = array ('test.php');
	
	foreach ($input_files as $file) {
		$input = file_get_contents ($file);
		if ($file == './../../core/i18n.class.php') continue;
		echo $file . "\n\n";
		switch (getFileExtension ($file)) {			
			case 'php':
				$pos = strpos ($input, 'translate (');
				while ($pos !== false) {
					$opener = $input[$pos+strlen ('translate (\'')-1] ;
					$pos += strlen ('translate (\'');
					$cB = trim (substr ($input, $pos));
					$codeBlock = substr ($cB, 0, strpos ($cB, $opener)); 
					$input = strstr (substr ($input, $pos), 'translate (');
					$pos = strpos ($input, 'translate (');
					$string = $codeBlock;					
					echo $string."\n";
					if (! array_key_exists ($string, $currentStrings)) {
						$currentStrings[$string] = '';
					}
				}			
				
				break;
			case 'tpl':
				preg_match_all ("/\{t s=[\"|']([\w|\s|.|(|)|%|:|>|<]*)[\"|']\}/", 
					$input, $matches);
				foreach ($matches[0] as $k=>$match) {
					$string = $matches[1][$k];
					if (! array_key_exists ($string, $currentStrings)) {
					//	$currentStrings[$string] = '';
					}
				}
				break;
		}
	}
	//print_r ($currentStrings);
	/*$h = fopen ($outputFile, "w");
	
	$output = "<?php \n";
	foreach ($currentStrings as $k=>$string) {
		$k = str_replace ("'", "\'", $k);
		$output .= '$strings[\''.$k.'\'] = \''.$string.'\';'."\n";
	}
	$output .= "?>";
	fwrite ($h, $output);
	fclose ($h);*/
	
} else {
	echo "HOWTO Use:\n";
	echo "php updatetranslation.php langCode outputdir input...\n";
	echo "input is automatically recursive\n";
}

?>