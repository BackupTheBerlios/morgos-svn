<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005-2006 MorgOS
 * This program is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * any later version.
 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Library General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. 
*/
/** \file compatible.php
 * File where all functions/constants/variables live that are in newer versions of PHP, but not in older ones, and that we want / can
 * to implement ourself. Some functions/constants/variables are utilities that we are using in the code and that are
 * not implemented in any PHP version.
 * \warning Some parts of this file can have no license, look to the function headers
 *
 * $Id$
 * \author Nathan Samson
*/
if (! function_exists ('file_get_contents')) {
	gettype ($_POST); // this is here only to trick Doxygen
	/** \fn file_get_contents ($fileName, $useIncludePath = false)
	 * Reads the entire file into a string. It returns false on an error.
	 * \warning It is not completely compatible with PHP 4.3 or PHP 5
	 *
	 * \param $fileName (string) the file you want to read in
	 * \param $useIncludePath true if you want to search also in the PHP include path, standard false
	 * \return string
	*/
	function file_get_contents ($fileName, $useIncludePath = false) {
		$fHandler = @fopen ($fileName, 'r', $useIncludePath);
		if ($fHandler !== false) {
			$buffer = NULL;
			while (! feof ($fHandler)) {
				$buffer .= fread ($fHandler, 4096); // we do not use filesize () because filesize doesn't search in include path
			}
			fclose ($fHandler);
			return $buffer;
		} else {
			return false;
		}
	}
}

if (! function_exists ('array_search')) {
	gettype ($_POST); // this is here only to trick Doxygen
	/** \fn array_search ($needle, $haystack, $strict = false)
	 * Searches the array for a given value and returns the corresponding key if successful.
	 * \warning all array key's are transformed to an int
	 * \warning the order of search is not set, so if you have 2 times the same value you
	 *  can't know which key you will get
	 *
	 * \param $needle (mixed) the value where you search for
	 * \param $haystack (array) the array where you search in
	 * \param $strict (bool) if true, the type is also checked
	 * \return (mixed)
	*/
	function array_search ($needle, $haystack, $strict = false) {
		foreach ($haystack as $key => $value) {
			if ($strict == true) {
				if ($value === $needle) {
					return $key;
				}
			} else {
				if ($value == $needle) {
					return $key;
				}
			}
		}
		return false;
	}
}

if (! function_exists ('trigger_error')) {
	gettype ($_POST); // this is here only to trick Doxygen
	/** \fn trigger_error ($error, $errorType = E_USER_NOTICE)
	 * Generates a user-level error/warning/notice message
	 *
	 * \param $error (string) The designated error message for this error. It's limited to 1024 characters in length. Any additional characters beyond 1024 will be truncated.
	 * \param $errorType (int) this is not used
	 * \return (bool)
	*/
	function trigger_error ($error, $errorType = E_USER_NOTICE) {
		return user_error ($error);
	}
}

if (! function_exists ('scandir')) {
	gettype ($_POST); // this is here only to trick Doxygen
	/** \fn scandir ($directory)
	 * List files and directories inside the specified path
	 * \warning this is not fully compatible with the one defined in PHP 5 (missing context param)
	 * \warning sorting doesn't happen with natural sorting order, you need to do this manually
	 *
	 * \param $directory (string)
	 * \param $sortingError (int) 1 if descending, otherwise ascending
	 * \return (array | false) 
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
			
			sort ($files);
	
			if ($sortingOrder == 1) {
				$files = array_reverse ($files);
			}
			
			return $files;
		}
	}
}

/** \fn versionCompare ($version1, $version2, $operator)
 * compares 2 version numbers. A version looks like "1.2.*" or "1.2" (which is the same)
 * \warning 1.2.0 > 1 does return false, this is intended
 *
 * \param $version1 (string)
 * \param $version2 (string)
 * \param $operator (string) >= <= > < == !=
 * \return (bool)
*/
function versionCompare ($version1, $version2, $operator) {
	$version1 = explode ('.', $version1);
	$version2 = explode ('.', $version2);
	$result = 0;
	foreach ($version1 as $key => $value) {
		if (! array_key_exists ($key, $version2)) {
			$result = 0;
			break;
		}
		if (($version1[$key] == '*') or ($version2[$key] == '*')) {
			$result = 0; 
		} elseif ($version1[$key] > $version2[$key]) {
			$result = 1;
			break;
		} elseif ($version1[$key] < $version2[$key]) {
			$result = -1;
			break;
		} else {
			$result = 0;
		}
	}
	
	switch ($operator) {
		case '>=':
			if ($result >= 0) {
				return true;
			} else {
				return false;
			}
		case '<=':
			if ($result <= 0) {
				return true;
			} else {
				return false;
			}
		case '>':
			if ($result > 0) {
				return true;
			} else {
				return false;
			}
		case '<':
			if ($result < 0) {
				return true;
			} else {
				return false;
			}
		case '==':
			if ($result == 0) {
				return true;
			} else {
				return false;
			}
		case '!=':
			if ($result != 0) {
				return true;
			} else {
				return false;
			}
		default:
			trigger_error ('ERROR: Operator doesn\'t exists.');
			return false;
	}
}

if (!function_exists('call_user_func_array')) {
	gettype ($_POST); // this is here only to trick Doxygen
	/** \fn call_user_func_array ($func, $args)
	 * Call a user function given with an array of parameters.
	 * \warning the license of this function is uknown, the code can be found on 
	 *  http://php.belnet.be/manual/en/function.call-user-func-array.php
	*/
	function call_user_func_array($func, $args) {
		$argString = '';
		$comma = '';
		for ($i = 0; $i < count($args); $i ++) {
			$argString .= $comma . "\$args[$i]";
			$comma = ', ';
		}

		if (is_array($func)) {
			$obj = &$func[0];
			$meth = $func[1];
			if (is_string($func[0])) {
				eval("\$retval = $obj::\$meth($argString);");
			} else {
				eval("\$retval = \$obj->\$meth($argString);");
			}
		} else {
			eval("\$retval = \$func($argString);");
		}
		return $retval;
	}
}

if (! defined ('NEWLINE')) {
	define ('NEWLINE', "\n");
}

if (! defined ('E_STRICT')) {
	define ('E_STRICT', 2048);
}
?>
