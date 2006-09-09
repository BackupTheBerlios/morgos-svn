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
/** \file varia.functions.php
 * Home of various functions.
 *
 * @since 0.2
 * @author Nathan Samson
*/

class Error {
	var $_error;
	var $_params;

	function Error ($error) {
		$this->_error = $error;
	}
	
	function is ($otherError) {
		if (isString ($otherError)) {
			return $this->_error == $otherError;
		} else {
			return $this->_error == $otherError->getError ();
		}
	}
	
	function getError () {return $this->_error;}
}

function isError ($test) {
	if (is_string ($test)) {
		if ((substr ($test, 0, 6) === "ERROR_") and (strlen ($test) > 6 )) {
			return true;
		} else {
			return false;
		}		
	} else {
		return false;
	}
}

/**
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



?>