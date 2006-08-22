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
/** \file compatible.functions.php
 * File that implements some functions that are in newer versions of PHP, but not in older.
 *
 * @since 0.2
 * @author Nathan Samson
*/

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
            
			sort ($files);
    
			if ($sortingOrder == 1) {
				$files = array_reverse ($files);
			}
            
			return $files;
		}
	}
}

?>