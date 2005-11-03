<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005 MorgOS
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
/** \file language.class.php
 * File that take care of the translatable strings
 *
 * \author Nathan Samson
*/
/** \class languages
 * Class that take care of the translatable strings
 *
 * \author Nathan Samson
 * \version 0.1svn
*/
class languages {
	
	function __construct () {
		$this->getAllSupportedLanguages ();
	}

	function languages () {
		$this->__construct ();
	}
	
	/** \fn getAllSupportedLanguages ()
	 * Returns all supported languages in an array
	 *
	 * \return (string array)
	*/
	/*public*/ function getAllSupportedLanguages () {
		if ($this->supported == NULL) {
			$supported = array ();
			$supported[] = 'english';
			$handler = opendir ('languages/');
			// $files = scandir ('languages/'); PHP5 only :( 
			// foreach ($files as $file) PHP5 only :(
			while (false !== ($file = readdir ($handler))) {
				// starts with a letter, then you have whatever you want and it ends with '.language.php'
				preg_match_all ('/^(\w.*)\.language\.php$/i', $file, $matches);
				foreach ($matches[0] as $key => $match) {
					$supported[] = $matches[1][$key];
				}
			}
			$this->supported = $supported;
		}
		return $this->supported;
	}
	
	/** \fn loadLanguage ($language)
	 * Loads the language. If the language is not supported returns false, otherwise true
	 *
	 * \param $language (string) the language you want to load
	 * \return (bool)
	*/
	/*public*/ function loadLanguage ($language) {
		if (in_array ($language, $this->supported)) {
			unset ($this->stringTree);
			unset ($strings);
			include ('languages/' . $language . '.language.php');
			$this->stringTree = $strings;
			return true;
		} else {
			return false;
		}
	}
	
	/** \fn translate ($string)
	 * It translates a string in the loaded language
	 *
	 * \param $string (string) the string you want to translate
	 * \return (string) the same tring but normally in another language, if it couldn't be translated the original string will be returned
	*/
	/*public*/ function translate ($string) {
		if (array_key_exists ($string, $this->stringTree)) {
			return $this->stringTree[$string];
		} else {
			return $string;
		}
	}
}