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
/** \file pluginmanager.class.php
 * File that take care of the plugins
 *
 * @ingroup interface
 * @since 0.2
 * @author Nathan Samson
*/

include_once ('core/compatible.functions.php');

/**
 * A class that represents a plugin
 *
 * @ingroup interface
 * @since 0.2
 * @author Nathan Samson
*/
class plugin {
	/**
	 * The name of the plugin
	 * @protected
	*/
	var $_name;
	/**
	 * The ID of the plugin
	 * @protected
	*/
	var $_ID;
	/**
	 * The dir where the plugin is located
	 * @protected
	*/
	var $_loadedDir;
	/**
	 * The minimal version of MorgOS
	 * @protected
	*/
	var $_minMorgOSVersion;
	/**
	 * The max version of MorgOS
	 * @protected
	*/
	var $_maxMorgOSVersion;
	/**
	 * The pluginAPI object
	 * @protected
	*/
	var $_pluginAPI;
	/**
	 * The plugin version
	 * @protected
	*/
	var $_version;
	
	
	/**
	 * Constructor
	 *
	 * @param $dir (string) see $_loadedDir 
	*/
	function plugin ($dir) {
		$this->_pluginAPI = null;
		$this->_loadedDir = $dir;
		$this->_minMorgOSVersion = MORGOS_VERSION;
		$this->_maxMorgOSVersion = MORGOS_VERSION;
	}	

	/**
	 * Loads the plugin.
	 *
	 * @param $pluginAPI (object)
	 * @public
	*/
	function load (&$pluginAPI) {
		$this->_pluginAPI = &$pluginAPI;
	}
	
	/**
	 * unloads the plugin
	 * @public
	*/
	function unLoad () {}	
	
	/**
	 * Checks that the plugin is loaded
	 *
	 * @public
	 * @return (bool)
	*/
	function isLoaded () {
		return $this->_pluginAPI != null;
	}
	
	/**
	 * Returns the ID
	 * @public
	 * @return (string)
	*/
	function getID () {return $this->_ID;}
	/**
	 * Returns the name
	 * @public
	 * @return (string)
	*/
	function getName () {return $this->_name;}
	/**
	 * Returns the dir where the plugin is located
	 * @public
	 * @return (string)
	*/
	function getLoadedDir () {return $this->_loadedDir;}
	/**
	 * Returns the version
	 * @public
	 * @return (string)
	*/
	function getVersion () {return $this->_version;}
	
	/**
	 * Returns that it is compatible or not
	 * @public
	 * @return (bool)
	*/
	function isCompatible () {
		return ($this->isPHPCompatible () && $this->isMorgOSCompatible ());
	}
	
	/**
	 * Returns that it is  with PHP
	 * @public
	 * @return (bool)
	*/
	function isPHPCompatible () {
		return true; // plugin should override it of not true
	}	
	
	/**
	 * Returns that it is compatible with MorgOS
	 * @public
	 * @return (bool)
	*/
	function isMorgOSCompatible () {
		if ($this->isMinVersionReached () and !$this->isMaxVersionExceeded ()) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Returns that the minimal version is reachhed
	 * @public
	 * @return (bool)
	*/
	function isMinVersionReached () {
		return versionCompare (MORGOS_VERSION, $this->_minMorgOSVersion, '>=');
	}
	
	/**
	 * Returns that the maximal version is exceeded. To have a working plugin this should be false
	 * @public
	 * @return (bool)
	*/
	function isMaxVersionExceeded () {
		return versionCompare (MORGOS_VERSION, $this->_maxMorgOSVersion, '>');
	}
	
	/**
	 * Returns that the plugin is a core plugin or not.
	 * @public
	 * @return (bool)
	*/
	function isCorePlugin () {return false;}
	
	function getPluginID () {
		$skinM = &$this->_pluginAPI->getSkinManager ();
		$plugSkinM = new skinManager ($this->_pluginAPI);
		$plugSkinM->findAllSkins ($this->getLoadedDir ().'/skins/');
		$IDLoadedSkin = $skinM->_loadedSkin[0]->getID ();
		if ($plugSkinM->existsSkin ($IDLoadedSkin)) {
			//$skin = $allSkins[$IDLoadedSkin];
			//return $this->getLoadedDir ().'/skins/'.$skin->_baseSkinDir;
			return $IDLoadedSkin;
		} else {
			return MORGOS_DEFAULTSKIN_ID;
		}
	}
	
	function isInstalled (&$foo) {return true;}
	function install (&$foo) {}
	function unInstall (&$foo) {}
}

/**
 * A class that manages the plugins
 *
 * @ingroup interface
 * @since 0.2
 * @author Nathan Samson
*/
class pluginManager {
	/**
	 * An array with all found plugins
	 * @private
	*/
	var $_foundPlugins;
	/**
	 * An array with all loaded plugins
	 * @private
	*/
	var $_loadedPlugins;
	/**
	 * An array with all plugins that should be loaded
	 * @private
	*/
	var $_pluginsToLoad;
	/**
	 * An pluginAPI object. All plugis may use all functions of this API
	 * @private
	*/
	var $_pluginAPI;
	
	/**
	 * Constructor
	 * @param $pluginAPI (object)
	*/
	function pluginManager (&$pluginAPI) {
		$this->_foundPlugins = array ();
		$this->_loadedPlugins = array ();
		$this->_pluginsToLoad = array ();
		$this->_pluginAPI = &$pluginAPI;
	}
	
	/**
	 * Search (and find) all plugins in one directory
	 *
	 * @param $dir (string)
	 * @public
	*/
	function findAllPlugins ($dir) {
		if (is_dir ($dir)) {
			foreach (scandir ($dir) as $file) {
				$fullFileName = $dir.'/'.$file;
				if (is_dir ($fullFileName)) {
					if (file_exists ($fullFileName.'/plugin.php')) {
						$pluginClass = '';
						include ($fullFileName.'/plugin.php');
						$plug = &new $pluginClass ($fullFileName);
						$this->_foundPlugins[$plug->getID ()] = &$plug;
					}
				}
			}
		} else {
			return new Error ('PLUGINMANAGER_DIR_NOT_FOUND', $dir);
		}
	}
	
	/**
	 * Set a plugin registered to be loaded
	 *
	 * @param $pluginID (string)
	 * @public
	*/
	function setPluginToLoad ($pluginID) {
		if ($this->existsPluginID ($pluginID)) {
			if ($this->_foundPlugins[$pluginID]->isMinVersionReached ()) {
				if (! $this->_foundPlugins[$pluginID]->isMaxVersionExceeded ()) {
					if ($this->_foundPlugins[$pluginID]->isCompatible ()) {
						$this->_pluginsToLoad[$pluginID] = $this->_foundPlugins[$pluginID];
					} else {
						return new Error ('PLUGINMANAGER_NOT_COMPATIBLE');
					}
				} else {
					return new Error ('PLUGINMANAGER_MAXVERSION_REACHED', $this->_foundPlugins[$pluginID]->_maxMorgOSVersion);
				}
			} else {
				return new Error ('PLUGINMANAGER_MINIMALVERSION_NOT_REACHED', $this->_foundPlugins[$pluginID]->_minMorgOSVersion);
			}
		} else {
			return new Error ('PLUGINMANAGER_PLUGIN_NOT_FOUND', $pluginID);
		}
	}
	
	/**
	 * Load all plugins that are registered to be loaded.
	 *
	 * @public
	*/
	function loadPlugins () {
		foreach ($this->_pluginsToLoad as $IDKey => $plugin) {
			if ($plugin->isInstalled ($this->_pluginAPI)) {		
				$result = $plugin->load ($this->_pluginAPI);
				if (! isError ($result)) {
					$sm = &$this->_pluginAPI->getSmarty ();
//					$sm->template_dir[] = 
					$this->_loadedPlugins[$IDKey] = $plugin;
					$this->_foundPlugins[$IDKey] = $plugin;
					// done for PHP 4, otherwise the updated plugin isn't saved to loadedPlguins
					
					if (file_exists ($plugin->getLoadedDir ().'/skins/')) {
						$skinM = &$this->_pluginAPI->getSkinManager ();
						$skinM->findAllSkins ($plugin->getLoadedDir ().'/skins/');
						$skinM->loadSkin ($plugin->getPluginID ());
					}
				} else {
					return $result;
				}
			}
		}
		$this->_pluginsToLoad = array ();
	}
	
	/**
	 * Unload the plugins
	 *
	 * @public
	*/
	function shutdown () {
		foreach ($this->_loadedPlugins as $plugin) {
			$plugin->unLoad ();
		}
	}
	
	/**
	 * Checks that a pluginID is found
	 *
	 * @param $pluginID (string)
	 * @public
	 * @return (bool)
	*/
	function existsPluginID ($pluginID) {
		return array_key_exists ($pluginID, $this->_foundPlugins);
	}	
	
	/**
	 * Return all found plugins
	 *
	 * @public
	 * @return (object plugin array)
	*/
	function getAllFoundPlugins () {
		return $this->_foundPlugins;
	}
	
	/**
	 * Return all loaded plugins
	 *
	 * @public
	 * @return (object plugin array)
	*/
	function getAllLoadedPlugins () {
		return $this->_loadedPlugins;
	}
	
	/**
	 * Return all loaded plugins ID
	 *
	 * @public
	 * @return (string plugin array)
	*/
	function getAllLoadedPluginsID () {
		$results = array ();
		foreach ($this->getAllLoadedPlugins () as $plugin) {
			$results[] = $plugin->getID ();
		}
		return $results;
	}
	
	/**
	 * Return a loaded plugin
	 *
	 * @param $ID (string)
	 * @public
	 * @return (object plugin)
	*/
	function getLoadedPlugin ($ID) {
		if ($this->existsLoadedPluginID ($ID)) {
			return $this->_loadedPlugins[$ID];
		} else {
			return new Error ('PLUGINMANAGER_PLUGIN_NOT_FOUND', $ID);
		}
	}
	
	/**
	 * Return a plugin
	 *
	 * @param $ID (string)
	 * @public
	 * @return (object plugin)
	*/
	function getPlugin ($ID) {
		if ($this->existsPluginID ($ID)) {
			return $this->_foundPlugins[$ID];
		} else {
			return new Error ('PLUGINMANAGER_PLUGIN_NOT_FOUND', $ID);
		}
	}
		
	
	/**
	 * Checks that a pluginID is loaded
	 *
	 * @param $ID (string)
	 * @public
	 * @return (bool)
	*/
	function existsLoadedPluginID ($ID) {
		return array_key_exists ($ID, $this->_loadedPlugins);
	}
}


?>