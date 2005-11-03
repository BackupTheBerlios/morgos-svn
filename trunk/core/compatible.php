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
/** \file compatible.php
 * File where all functions live that are in newer versions of PHP, but not in older ones, and that we want / can
 * to implement ourself.
 *
 * \author Nathan Samson
*/
if (! function_exists ('file_get_contents')) {
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
			return $buffer;
			fclose ($fHandler);
		} else {
			return false;
		}
	}
}
?>
