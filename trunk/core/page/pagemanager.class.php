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
/** \file pagemanager.class.php
 * Manager of the pages.
 *
 * @since 0.2
 * @author Nathan Samson
*/

include_once ('core/page/page.class.php');

class pageManager {
	/**
	 * The database module.
	 * @private
	*/
	var $db;
	
	/**
	 * Cached array for options for a page
	*/
	var $allOptionsForPage;

	/**
	 * Constructor
	 *
	 * @param $db (obejct dbModule)
	*/
	function pageManager ($db) {
		$this->db = $db;
		$this->allOptionsForPage = null;
	}
	
	function addPageToDatabase () {
	}
	
	/**
	 * Creates a new page object.
	 *
	 * @public
	 * @return (object page)
	*/
	function newPage () {
		return new page ($this->db, $this->getAllOptionsForPage (), $this);
	}
	
	/**
	 * Returns an array with all menu items. The first item in the array is the first menu item (duh)
	 * 
	 * @param $rootPage (object page) The root page.
	 * @public
	 * @return (object page array)
	*/	
	function getMenu ($rootPage) {
		$tableName = $this->db->getPrefix ().'pages';
		$parentPageID = $rootPage->getID ();
		$sql = "SELECT pageID FROM $tableName WHERE parentPageID='$parentPageID' ORDER BY placeInMenu ASC";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$allPages = array ();
			while ($pageRow = $this->db->fetchArray ($q)) {
				$newPage = $this->newPage ();
				$newPage->initFromDatabaseID ($pageRow['pageID']);
				$allPages[] = $newPage;
			}
			return $allPages;
		} else {
			return $q;
		}
	}
	
	/**
	 * Adds an extra option to the database for the pages.
	 *
	 * @param $newOption (string) the name of the new option
	 * @param $sqlType (string) the sqltype possible options: 
	 *   - Varchar (length)
	 *   - Int
	 *   - enum('a', 'b', 'c')
	 *   - text
	 * @warning old page objects don't profit of this. 
	 *  Wait for a restart of the system (reload of the page) to be sure its applied.
	 * @bug when after adding one, someone ask wich exists the new is not added in.
	 *    if that "asker" want to do something with it on an old page object it can cause weird errors.
	 * @public
	*/
	function addOptionToPage ($newOption, $sqlType) {
		$curOptions = $this->getAllOptionsForPage ();
		if (! isError ($curOptions)) {
			if (! array_key_exists ($newOption, $curOptions)) {
				$prefix = $this->db->getPrefix ();
				$sql = "ALTER TABLE ".$prefix."pages ADD $newOption $sqlType";
				$q = $this->db->query ($sql);
				if (isError ($q)) {
					return $q;
				}
				$this->allOptionsForPage[$newOption] = null;
			} else {
				return "ERROR_PAGEMANAGER_OPTION_FORPAGE_EXISTS $newOption";
			}
		} else {
			return $curOptions;
		}
	}
	
	/**
	 * Removes an extra option to the database for the pages.
	 *
	 * @param $optionName (string) the name of the option
	 * @warning old page objects don't profit of this. 
	 *  Wait for a restart of the system (reload of the page) to be sure its applied.
	 * @public
	*/
	function removeOptionForPage ($optionName) {
		$curOptions = $this->getAllOptionsForPage ();
		if (! isError ($curOptions)) {
			if (array_key_exists ($optionName, $curOptions)) {
				$prefix = $this->db->getPrefix ();
				$sql = "ALTER TABLE ".$prefix."pages DROP $optionName";
				$q = $this->db->query ($sql);
				if (isError ($q)) {
					return $q;
				}
				unset ($this->allOptionsForPage[$optionName]);
			} else {
				return "ERROR_PAGEMANAGER_OPTION_FORPAGE_DOESNT_EXISTS $optionName";
			}
		} else {
			return $curOptions;
		}
	}
	
	/**
	 * Returns an associative array with values null, and keys the name of the option
	 *
	 * @return (null array)
	 * @public
	*/
	function getAllOptionsForPage () {
		if ($this->allOptionsForPage === null) {
			$fields = $this->db->getAllFields ($this->db->getPrefix ().'pages');
			if (! isError ($fields)) {
				$allOptions = array ();
				foreach ($fields as $field) {
					if (! ($field == 'pageID' or $field == 'genericName' or $field == 'genericContent' or $field == 'placeInMenu' or $field == 'parentPageID')) {
						$allOptions[$field] = null;
					}
				}
				$this->allOptionsForPage = $allOptions;
				return $allOptions;
			} else {
				return $fields;
			}
		} else {
			return $this->allOptionsForPage;
		}
	}

}
