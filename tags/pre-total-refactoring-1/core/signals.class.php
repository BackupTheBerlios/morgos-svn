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
/** \file signals.class.php
 * File that take care of the signals subclass
 *
 * $Id$
 * \author Nathan Samson
*/
/** \class signalManager
 * File that take care of the signals subclass
 *
 * \author Nathan Samson
 * \version 0.1svn
*/
class signalManager {
	function signalManager (&$i10nMan) {
		$this->__construct ($i10nMan);
	}
	
	function __construct (&$i10nMan) {
		$this->i10nMan = &$i10nMan;
		$this->signals = array ();
	}
	
	/** \fn addSingal ($name)
	 * Add a signal, after this you can execute it or add events to it.
	 *
	 * \param $name (string)
	*/
	function addSignal ($name) {
 		if (! array_key_exists ($name, $this->signals)) {
			$this->signals[$name] = array ();
		} else {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('This signal already exists.'));
		}
	}
	
	/** \fn execSignal ($name)
	 * Executes a signal, and all its callbacks
	 *
	 * \param $name (string)
	*/
	function execSignal ($name) {
		if (array_key_exists ($name, $this->signals)) {
			foreach ($this->signals[$name] as $callback) {
				$params = $callback[1];
				for ($i = 1; $i < func_num_args (); $i++) {
					$params[] = func_get_arg ($i);
				}
				call_user_func_array ($callback[0], $params);
			}
		} else {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('This signal doesn\'t exists.'));
		}
	}
	
	/** \fn connectSignal ($name, $callback, &$params)
	 * Connect a callback to a function.
	 *
	 * \param $name (string)
	 * \param $callback (callback)
	 * \params $param (array)
	*/ // can't be &$params = array () PHP 4.? doesn't support it :(
	function connectSignal ($name, $callback, &$params) {
		if (array_key_exists ($name, $this->signals)) {
			$this->signals[$name][] = array ($callback, &$params);
		} else {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('This signal doesn\'t exists.'));
		}
	}
}
?>