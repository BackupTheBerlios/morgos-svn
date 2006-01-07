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
/** \file pages.class.php
 * File that take care of the modules and pages
 *
 * \author Nathan Samson
*/

/** \class pages
 * class that take care of the modules and pages
 *
 * \author Nathan Samson
 * \version 0.1svn
 * \bug If a module is deleted but not all pages are deleted this pages are not deleted
*/
class pages {
	function pages (&$genDB, &$i10nMan) {
		$this->construct ($genDB, $i10nMan);
	}

	function __construct (&$genDB, &$i10nMan) {
		$this->genDB = &$genDB;
		$this->i10nMan = &$i10nMan;
	}
	/** \fn getAllAvailableModules ($extended = false)
	 * Returns an array of all available modules
	 *
	 * \param $extended (bool) search to in INTERNAL_MODULES (default is false)
	 * \return (string array)
	*/
	/*public*/ function getAllAvailableModules ($extended = false) {
		$SQL = 'SELECT * FROM ' . TBL_MODULES;
		if ($extended == false) {
			$SQL .= " WHERE listedinadmin='yes'";
		}
		$available = array ();
		$result = $this->genDB->query ($SQL);
		while ($row = $this->genDB->fetch_array ($result)) {
			$available[$row['module']] = $row;
		}
		return $available;
	}
	
	/** \fn getPageContent ($module, $language)
	 * Returns the content of a module
	 *
	 * \param $module (string) the name of the module
	 * \param $language (string) the language
	 * \return (string)
	*/
	/*public*/ function getPageContent ($module, $language) {
		$module = addslashes ($module);
		$SQL = 'SELECT content FROM ' . TBL_PAGES . ' WHERE module=\''. $module . '\'  AND language=\'' . $language . '\'';
		$result = $this->genDB->query ($SQL);
		if ($this->genDB->num_rows ($result) != 0) {
			$row = $this->genDB->fetch_array ($result);
			$content = $row['content'];
			foreach ($this->prependers[$module] as $text) {
				$content = $text . $content;
			}
			
			foreach ($this->appenders[$module] as $text) {
				$content = $content . $text;
			}
			
			foreach ($this->prependers['ALL_MODULES'] as $text) {
				$content = $text . $content;
			}
			
			foreach ($this->appenders['ALL_MODULES'] as $text) {
				$content = $content . $text;
			}
			return $content;
		} else {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('Page not found.'));
			return false;
		}
	}
	
	/** \fn changeSettingsModule ($module, $needAuthorize, $adminOnly)
	 * changes the settings for a module
	 *
	 * \param $module (string)
	 * \param $needAuthorize (bool) 
	 * \param $adminOnly (bool) 
	*/
	/*public*/ function changeSettingsModule ($module, $needAuthorize, $adminOnly) {
		if ($needAuthorize) {
			$needAuthorize = 'yes';
		} else {
			$needAuthorize = 'no';
		}
		
		if ($adminOnly) {
			$adminOnly = 'yes';
		} else {
			$adminOnly = 'no';
		}
		$SQL = "UPDATE " . TBL_MODULES . " SET needauthorized='$needAuthorize', needauthorizedasadmin='$adminOnly' WHERE module='$module'";
		$this->genDB->query ($SQL);
	}
	
	/** \fn addModule ($module, $needAuthorize, $needAuthorizeAsAdmin, $place, $placeinadmin, $listedInAdmin = true, $parent = NULL)
	 * adds a module,
	 *
	 * \param $module (string)
	 * \param $needAuthorize (bool)
	 * \param $needAuthorizeAsAdmin (bool)
	 * \param $place (int) the place in the navigator, if 0 is not listed
	 * \param $placeinadmin (int) the place in the admin navigator, if 0 is not listed
	 * \param $listedInAdmin (bool) if this needs to be listed in the admin
	 * \param $parent (string) the parent module, if NULL it is the root (multiple roots are possible)
	 * \return (bool) 
	*/
	/*public*/ function addModule ($module, $needAuthorize, $needAuthorizeAsAdmin, $place, $placeinadmin, $listedInAdmin = true, $parent = NULL) {
		if (array_key_exists ($module, $this->getAllAvailableModules ())) {
			return false;
		} else {
			if ($needAuthorize) {
				$needAuthorize = 'yes';
			} else {
				$needAuthorize = 'no';
			}
			if ($needAuthorizeAsAdmin) {
				$needAuthorizeAsAdmin = 'yes';
			} else {
				$needAuthorizeAsAdmin = 'no';
			}
			if ($listedInAdmin == true) {
				$listedInAdmin = 'yes';
			} else {
				$listedInAdmin = 'no';
			}
			if (! is_integer ($place)) {
				trigger_error ('ERROR: ' . $this->i10nMan->translate ('Place is not an integer'));
				return;
			}		
			if (! is_integer ($placeinadmin)) {
				trigger_error ('ERROR: ' . $this->i10nMan->translate ('Place is not an integer'));
				return;
			}	
			$SQL = "INSERT INTO " . TBL_MODULES;
			$SQL .= " (module,needauthorized,needauthorizedasadmin, listedinadmin, place, placeinadmin, parent)";
			$SQL .= " VALUES ('$module','$needAuthorize','$needAuthorizeAsAdmin', '$listedInAdmin', '$place', '$placeinadmin', '$parent')";
			$result = $this->genDB->query ($SQL);
			if ($result !== false) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	/** \fn addPage ($module, $language, $name, $content)
	 * adds a page
	 *
	 * \param $module (string)
	 * \param $language (string)
	 * \param $name (string)
	 * \param $content (string)
	 * \return (bool) 
	*/
	/*public*/ function addPage ($module, $language, $name, $content) {
		if (array_key_exists ($language, $this->getAllAvailableLanguagesFromModule ($language))) {
			return false;
		} else {
			$module = addslashes ($module);
			$language = addslashes ($language);
			$name = addslashes ($name);
			$content = addslashes ($content);
			$SQL = "INSERT into " . TBL_PAGES . " (module,name,language,content) VALUES ('$module','$name','$language','$content')";
			$result = $this->genDB->query ($SQL);
			if ($result !== false) {
				return true;
			} else {
				trigger_error ('INTERNAL_ERROR: ' . $this->i10nMan->translate ('Can\'t add page'));
				return false;
			}
		}
	}
	
	/** \fn deletePage ($module, $language)
	 * deletes a page
	*/
	/*pulbic*/ function deletePage ($module, $language) {
		$SQL = "DELETE FROM " . TBL_PAGES . " WHERE module='$module' AND language='$language'";
		$result = $this->genDB->query ($SQL);
		if ($result !== false) {
			return false;
		} else {
			return true;
		}
	}
	
	/** \fn deleteModule ($module)
	 * deletes a module
	*/
	/*pulbic*/ function deleteModule ($module) {
		$SQL = "DELETE FROM " . TBL_MODULES . " WHERE module='$module'"; 
		$result = $this->genDB->query ($SQL);
		if ($result !== false) {
			return true;
		} else {
			return false;
		}
	}
	
	/** \fn getPageInfo ($module, $language)
	 * get all the page info
	 *
	 * \param $module (string) the module
	 * \param $language (string) the language
	 * \return (bool | string array)
	*/
	/*pulbic*/ function getPageInfo ($module, $language) {
		$SQL = "SELECT * FROM " . TBL_PAGES . " WHERE module='$module' AND language='$language'"; 
		$result = $this->genDB->query ($SQL);
		if ($result !== false) {
			return $this->genDB->fetch_array ($result);
		} else {
			return false;
		}
	}
	
	/** \fn editPage ($module, $language, $newName, $newContent)
	 * edit the page
	 *
	 * \param $module (string)
	 * \param $language (string)
	 * \param $newName (string)
	 * \param $newContent (string)
	 * \return (bool)
	*/
	/*pulbic*/ function editPage ($module, $language, $newName, $newContent) {
		$SQL = "UPDATE " . TBL_PAGES . " SET name='$newName', content='$newContent' WHERE module='$module' AND language='$language'"; 
		$result = $this->genDB->query ($SQL);
		if ($result !== false) {
			return true;
		} else {
			return false;
		}
	}
	
	/** \fn prependTextToPageContent ($string, $toModule = NULL)
	 * prepends text to the page content.
	 *
	 * \param $string (string) the string to prepend
	 * \param $toModule (string) all modules where you want to add the text
	*/
	/*public*/ function prependTextToPageContent ($string, $toModule = NULL) {
		if ($toModule == NULL) {
			$toModule = 'ALL_MODULES';
		}
		$this->prependers[$toModule][] = $string;
	}	
	
	/** \fn appendTextToPageContent ($string, $toModule = NULL)
	 * Appends text to the page content.
	 *
	 * \param $string (string) the string to append
	 * \param $toModule (string) all modules where you want to add the text
	*/
	/*public*/ function appendTextToPageContent ($string, $toModule = NULL) {
		if ($toModule == NULL) {
			$toModule = 'ALL_MODULES';
		}
		$this->appenders[$toModule][] = $string;
	}
		
	/** \fn getAllAvailableLanguagesFromModule ($module)
	 * Returns the language of all pages with a specified module
	 *
	 * \return (string array)
	*/
	/*private*/ function getAllAvailableLanguagesFromModule ($module) {
		$available = array ();
		$SQL = "SELECT language FROM ". TBL_PAGES . " WHERE module='$module'";
		$result = $this->genDB->query ($SQL);
		while ($page = $this->genDB->fetch_array ($result)) {
			$available[] = $page['language'];
		}
		return $available;
	}
}