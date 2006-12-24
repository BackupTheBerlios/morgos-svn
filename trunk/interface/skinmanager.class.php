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
/** \file skinmanager.class.php
 * File that take care of the skins
 *
 * @ingroup interface
 * @since 0.2
 * @author Nathan Samson
*/

class skin {
	var $_ID;
	var $_name;
	var $_version;
	var $_minMorgOSVersion;
	var $_maxMorgOSVersion;
	var $_baseDir;
	var $_baseSkinDir;
	
	function skin ($skinFile, $baseDir, $baseSkinDir) {
		$this->_baseDir = $baseDir;
		$this->_baseSkinDir = $baseSkinDir;
		include ($skinFile);
		$this->_ID =  $SkinID;
		$this->_name = $SkinName;
		$this->_version = $SkinVersion;
		$this->_minMorgOSVersion = $SkinMinMorgOSVersion;
		$this->_maxMorgOSVersion = $SkinMaxMorgOSVersion;
	}
	
	function canRun () {
		if (! file_exists ($this->getCompileDir ())) {
			return new Error ('');
		}
		if (! is_dir ($this->getCompileDir ())) {
			return new Error ('');
		}
		if (! is_writable ($this->getCompileDir ())) {
			return new Error ('');
		}
	}
	
	function getDir () {
		return $this->_baseDir. $this->_baseSkinDir;
	}
	
	function getCompileDir () {
		return 'skins_c/'. $this->_baseSkinDir;
	}
	
	function getCacheDir () {
		return 'cache/'. $this->_baseSkinDir;
	}
	
	function getConfigDir () {
		return 'config/'. $this->_baseSkinDir;
	}
	
	function getID () {return $this->_ID;}
	function getName () {return $this->_name;}
	
	function createCompileDir () {
		if (! file_exists ($this->getCompileDir ())) {
			@mkdir ($this->getCompileDir ());
		}
	}
}

class skinManager {
	var $_allFoundSkins;
	var $_loadedSkin;
	var $_pluginAPI;
	
	function skinManager (&$pluginAPI) {
		$this->_pluginAPI = &$pluginAPI;
		$this->_loadedSkin = array ();
		$this->_allFoundSkins = array ();
	}
	
	function findAllSkins ($dir) {
		foreach (scandir ($dir) as $dirName) {
			if (is_dir ($dir.$dirName)) {
				if (file_exists ($dir.$dirName.'/skin.php')) {
					$skin = new skin ($dir.$dirName.'/skin.php', $dir, $dirName);
					if (! isError ($skin->canRun ())) {
						$this->_allFoundSkins[$skin->getID ()] = $skin;
					} else {
						$skin->createCompileDir ();
						if (! isError ($skin->canRun ())) {
							$this->_allFoundSkins[$skin->getID ()] = $skin;
						}
					}
				}
			}
		}
	}
	
	function loadSkin ($skinID) {
		if ($this->existsSkin ($skinID)) {
			$skin = $this->_allFoundSkins[$skinID];
			$sm = &$this->_pluginAPI->getSmarty ();
			$sm->template_dir[] = $skin->getDir ();
			//$sm->config_dir = $skin->getConfigDir ();
			if ($this->_loadedSkin == array ()) {
				$sm->compile_dir = $skin->getCompileDir ();
				$sm->cache_dir = $skin->getCacheDir ();
				$sm->assign ('SkinPath', $skin->getDir ());
			}
			$this->_loadedSkin[] = $skin;
		} else {
			var_dump ($this->_allFoundSkins);
			return new Error ('SKINMANAGER_SKIN_NOT_FOUND');
		}
	}
	
	function getFoundSkinsArray () {
		$skins = array ();
		foreach ($this->_allFoundSkins as $skin) {
			$skins[] = array ('ID'=>$skin->getID (), 'Name'=>$skin->getName ());
		}
		return $skins;
	}
	
	function existsSkin ($skinID) {
		return array_key_exists ($skinID, $this->_allFoundSkins);
	}

}

?>
