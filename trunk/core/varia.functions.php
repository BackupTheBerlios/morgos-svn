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
 * @ingroup core
 * @since 0.2
 * @author Nathan Samson
*/

/**
 * The core is the heart of MorgOS
 *
 * @defgroup core Core
*/

define ('PHP_NL', "\n");

/**
 * A class that represents an error
 *
 * @ingroup core
 * @since 0.2
 * @author Nathan Samson
*/
class Error {
	var $_error;
	var $_params;

	function Error ($error) {
		$this->_error = $error;
		for ($i = 1; $i < func_num_args (); $i++) {
			$this->_params[$i-1] = func_get_arg ($i);
		}
	}
	
	function is ($otherError) {
		if (is_string ($otherError)) {
			return $this->_error == $otherError;
		} else {
			return $this->_error == $otherError->getError ();
		}
	}
	
	function getError () {return $this->_error;}
	
	function getParam ($n) {
		return $this->_params[$n-1];
	}
	
	function getParams () {return $this->_params;}
}

/**
 * Cheks that a var is an error
 *
 * @ingroup core
 * @since 0.2
 * @param $test (mixed) the var to test
 * @return (bool)
*/
function isError ($test) {
	if (strtolower (get_class ($test)) == 'error') {
		return true;
	} else {
		return false;
	}
}

/**
 * compares 2 version numbers. A version looks like "1.2.*" or "1.2" (which is the same)
 * @warning 1.2.0 > 1 does return false, this is intended
 *
 * @ingroup core
 * @param $version1 (string)
 * @param $version2 (string)
 * @param $operator (string) >= <= > < == !=
 * @return (bool)
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
			return ($result >= 0);
		case '<=':
			return ($result <= 0);
		case '>':
			return ($result > 0);
		case '<':
			return ($result < 0);
		case '==':
			return ($result == 0);
		case '!=':
			return ($result != 0);
		default:
			return new Error ('VERSION_COMPARE_OPERATOR_DOESNT_EXISTS');
	}
}

/**
 * Strip all unwanted tags/attribues in HTML input
 *
 * @ingroup core
 * @since 0.2
 * @param $in (string)
 * @return (string)
*/
function secureHTMLInput ($in) {
	$in = strip_tags ($in, '<h1><h2><h3><h4><h5><h6><pre><p><a><em><strong><u><ul><ol><li><br><hr><img><sup><sub><blockquote><table><tr><td><th><tbody><thead><tfoot>');
	$in = stripslashes ($in);
	$stripAttrib = "'\\s(onblur)=\"(.*?)\"'i";
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onchange)=\"(.*?)\"'i";
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onclick)=\"(.*?)\"'i";
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onerror)=\"(.*?)\"'i";
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onfocus)=\"(.*?)\"'i";
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onkeydown)=\"(.*?)\"'i";
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onkeypress)=\"(.*?)\"'i";
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onkeyup)=\"(.*?)\"'i";
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onload)=\"(.*?)\"'i"; 
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onmousedown)=\"(.*?)\"'i"; 
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onmousemove)=\"(.*?)\"'i"; 
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onmouseout)=\"(.*?)\"'i"; 
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onmouseover)=\"(.*?)\"'i"; 
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onmouseup)=\"(.*?)\"'i"; 
	$in = preg_replace ($stripAttrib, '', $in);
	$stripAttrib = "'\\s(onsubmit)=\"(.*?)\"'i"; 
	$in = preg_replace ($stripAttrib, '', $in);
	addslashes ($in);
	return $in;
}

/**
 * Strip all not given attributes for a specific tag in a string.
 *
 * @ingroup core
 * @since 0.2
 * @param $msg (string) A string
 * @param $tag (string) the tag where it counts
 * @param $attr (string array) attributes that may exist in that tag.
 * @return (string)
*/
function stripAttributes ($msg, $tag, $attr) {
}

function morgosBacktrace () {
	
	echo '<table>';
	foreach (debug_backtrace () as $trace) {
		if (strtolower ($trace['function']) == 'morgosbacktrace') {
			continue;
		}
		echo '<tr>';
			echo '<td>'.$trace['function'].'</td>';
			echo '<td>'.$trace['file'].'</td>';
			echo '<td>'.$trace['line'].'</td>';
			foreach ($trace['args'] as $arg) {
				if (is_object ($arg)) {
					echo '<td>'.$arg->getType ().'</td>';
				} elseif (is_array ($arg)) {
					echo '<td>'.$arg.'</td>';
				} else {
					echo '<td>';
					var_dump ($arg);
					echo '</td>';
				}
			}
		echo '</tr>';
	}
	
	echo '</table>';
	exit;
}

?>