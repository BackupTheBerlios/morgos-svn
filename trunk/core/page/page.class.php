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
/** \file page.class.php
 * Manager of the pages.
 *
 * @since 0.2
 * @author Nathan Samson
*/

class page extends databaseObject {

	/**
	 * Constructor
	 *
	 * @param $db (object dbModule)
	 * @param $allOptions (null array)
	 * @param $parent (object) The creator
	*/	
	function page ($db, $allOptions, &$parent) {
		parent::databaseObject ($db, $allOptions, array ('genericName', 'genericContents', 'parentPageID', 'placeInMenu'), 'pages', 'pageID', $parent);
	}

	/**
	 * Initializes the page from a generic name
	 *
	 * @param $genericName (string) The generic name
	 * @public
	*/
	function initFromGenericName ($genericName) {
		$fullTableName = $this->getFullTableName ();
		$sql = "SELECT * FROM $fullTableName WHERE genericName='$genericName'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$row = $this->db->fetchArray ($q);
			$this->initFromArray ($row);
			$this->setOption ('ID', $row[$this->getIDName ()]);
		} else {
			return $q;
		}
	}
	
	/**
	 * Returns the content
	 *
	 * @public
	 * @return (string)
	*/
	function getGenericContent () {return $this->getOption ('genericContent');}
	
	/**
	 * Returns the generic name (title) of the page
	 *
	 * @public
	 * @return (string)
	*/
	function getGenericName () {return $this->getOption ('genericName');}
	
	/**
	 * Returns the ID of the parentPage. 0 if it is a root element.
	 *
	 * @public
	 * @return (int)
	*/
	function getParentPageID () {return $this->getOption ('parentPageID');}
	
	/**
	 * Returns the parentPage.
	 *
	 * @public
	 * @return (object)
	*/
	function getParentPage () {
		$parentPage = $parent->newPage ();
		$parentPage->initFromPageID ($this->getParentPageID ());
		return $parentPage;
	}
}
?>