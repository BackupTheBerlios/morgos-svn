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
/** \file i18n.class.php
 * Manager of the localization system.
 *
 * @ingroup core i18n
 * @since 0.2
 * @author Nathan Samson
*/

/**
 * A class that translate strings
 *
 * @ingroup core i18n
 * @since 0.2
 * @author Nathan Samson
*/
class localizer {
	var $_strings;
	var $_knownErrors;
	var $_lang;

	function localizer () {
		$this->_strings = array ();
		$this->_knownErrors = array ();
		$this->_lang = 'en_UK';
	}
	
	function getLanguage () {
		return $this->_lang;
	}
	
	function loadLanguage ($language, $rootDir) {
		$this->_lang = $language;
		return $this->loadTranslation ($rootDir);
	}
	
	function loadTranslation ($dir) {
		$file = $dir.'/'.$this->getLanguage ().'.trans.php'; 
		if (file_exists ($file)) {
			$strings = array ();
			$errorStrings = array ();
			include ($file);
			$this->_strings = array_merge ($this->_strings, $strings);
		} else {
			return new Error ('LANGUAGE_FILE_NOT_FOUND',$file);
		}
	}

	function replaceParams ($s, $p) {
		foreach ($p as $key=>$string) {
			$s = str_replace ('%'.($key+1), $string, $s);
		}
		return $s;
	}

	function translate ($s, $params = array ()) {
		if (array_key_exists ($s, $this->_strings)) {
			$o = $s;
			$s = $this->_strings[$s];
			if ($s == null) {
				return $this->replaceParams ($o, $params);
			}

			return $this->replaceParams ($s, $params);
		} else {
			return $this->replaceParams ($s, $params);
		}
	}
	
	function translateError ($error) {
		if (array_key_exists ($error->getError (), $this->_knownErrors)) {
			$s = $this->_knownErrors[$error->getError ()];
			return $this->translate ($s, $error->getParams ());
		} else {
			return $this->replaceParams (
				'Unexpected error. (%1)', array ($error->getError ()));
		}
	}
	
	function addError ($errorString, $tString) {
		$this->_knownErrors[$errorString] = $tString;
	}
}
