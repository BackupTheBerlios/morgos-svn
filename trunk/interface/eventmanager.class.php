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
/** \file eventmanager.class.php
 * File that take care of the eventmanager
 *
 * @since 0.2
 * @author Nathan Samson
*/

class callback {
	/**
	 * The name of the callback
	 * @private
	*/
	var $_name;
	/**
	 * The PHP callback
	 * @private
	*/
	var $_phpcallback;
	/**
	 * The arguments of the function
	 * @private
	*/
	var $_arguments;
	
	/**
	 * Constructor
	 *
	 * @param $name (string)
	 * @param $phpcallback (string | mixed array)
	 * @param $arguments (mixed array) Default is empty
	*/
	function callback ($name, $phpcallback, $arguments = array ()) {
		$this->_name = $name;
		$this->_phpcallback = $phpcallback;
		$this->_arguments = $arguments;
	}	
	
	/**
	 * Executes the callback
	 *
	 * @param $eventParams (mixed array)
	 * @public
	 * @return (mixed)
	*/
	function execute ($eventParams) {
		foreach ($this->_arguments as $i=>$value) {
			if (is_string ($value) or (is_int ($value))) {
				if (array_key_exists ($value, $eventParams)) {
					$this->_arguments[$i] = $eventParams[$value];
				}
			}
		}
		return call_user_func_array ($this->_phpcallback, $this->_arguments);
	}
	
	/**
	 * Returns the name of the callback
	 * @public
	 * @return (string)
	*/
	function getName () {return $this->_name;}
}

class event {
	/**
	 * The list of all callbacks
	 * @private
	*/
	var $_callbacksList;
	/**
	 * The name of the event
	 * @private
	*/
	var $_name;
	/**
	 * List of paramnames
	 * @private
	*/
	var $_paramList;
	
	function event ($name, $paramList = array ()) {
		$this->_name = $name;
		$this->_callbacksList = array ();
		$this->_paramList = $paramList;
	}

	/**
	 * Triggers the callback
	 *
	 * @param $paramValues (mixed array)
	 * @public
	 * @return (mixed array) The return values of the callbacks
	*/
	function trigger ($paramValues = array ()) {	
		$assParams = array ();
		foreach ($this->_paramList as $i=>$name) {
			$assParams[$name] = $paramValues[$i];
		}
		$returns = array ();
		foreach ($this->_callbacksList as $name => $callback) {
			$returns[$name] = $callback->execute ($assParams);
		}
		return $returns;
	}
	
	/**
	 * Removes a callback from a function
	 *
	 * @param $callbackName (string)
	 * @public
	*/
	function removeCallback ($callbackName) {
		if ($this->existsCallback ($callbackName)) {
			unset ($this->_callbacksList[$callbackName]);
		} else {
			return "ERROR_EVENT_CALLBACK_DOESNT_EXISTS $callbackName";
		}
	}
	
	/**
	 * Adds a callback
	 *
	 * @param $callback
	 * @public
	*/
	function addCallback ($callback) {
		if (! $this->existsCallback ($callback->getName ())) {
			$this->_callbacksList[$callback->getName ()] = $callback;
		} else {
			return "ERROR_EVENT_CALLBACK_EXISTS {$callback->getName ()}";
		}
	}
	
	/**
	 * Returns that a callback exists
	 *
	 * @param $callbackName (string)
	 * @public
	 * @return (bool)
	*/
	function existsCallback ($callbackName) {
		return array_key_exists ($callbackName, $this->_callbacksList);
	}
	
	/**
	 * Returns the name of the event.
	 * @public
	 * @return (string)
	*/
	function getName () {return $this->_name;}
}

class eventManager {
	/**
	 * The list of all events. The key is the name of the event.
	 * @private
	*/
	var $_eventsList;

	/**
	 * Constructor
	*/
	function eventManager () {
		$this->_eventsList = array ();
	}

	/**
	 * Subscribes to an event
	 *
	 * @param $eventName (string)
	 * @param $callback (object callback)
	 * @public
	*/
	function subscribeToEvent ($eventName, $callback) {
		if ($this->existsEvent ($eventName)) {
			return $this->getEvent ($eventName)->addCallback ($callback);
		} else {
			return "ERROR_EVENTMANAGER_EVENT_DOESNT_EXISTS $eventName";
		}
	}
	
	/**
	 * Unsunscribes from an event
	 *
	 * @param $eventName (string)
	 * @param $callback (object callback)
	 * @public
	*/
	function unsubscribeFromEvent ($eventName, $callback) {
		if ($this->existsEvent ($eventName)) {
			return $this->getEvent ($eventName)->removeCallback ($callback->getName ());
		} else {
			return "ERROR_EVENTMANAGER_EVENT_DOESNT_EXISTS $eventName";
		}
	}
	
	/**
	 * Adds an event
	 *
	 * @param $event (object event)
	 * @public
	*/
	function addEvent ($event) {
		if (! $this->existsEvent ($event->getName ())) {
			$this->_eventsList[$event->getName ()] = $event;
		} else {
			return "ERROR_EVENTMANAGER_EVENT_EXISTS {$event->getName ()}";
		}
	}
	
	/**
	 * Remove an event
	 *
	 * @param $eventName (string)
	 * @public
	*/
	function removeEvent ($eventName) {
		if ($this->existsEvent ($eventName)) {
			unset ($this->_eventsList[$eventName]);
		} else {
			return "ERROR_EVENTMANAGER_EVENT_DOESNT_EXISTS $eventName";
		}
	}
	
	/**
	 * Triggers an event
	 *
	 * @param $eventName (string) The event to trigger
	 * @param $eventParams (mixed array) the event params
	 * @public
	 * @return (mixed array) the return values of the callbacks
	*/
	function triggerEvent ($eventName, $eventParams = array ()) {
		if ($this->existsEvent ($eventName)) {
			return $this->getEvent ($eventName)->trigger ($eventParams);
		} else {
			return "ERROR_EVENTMANAGER_EVENT_DOESNT_EXISTS $eventName";
		}
	}
	
	/**
	 * Checks that an event exists
	 *
	 * @param $eventName (string)
	 * @public
	 * @return (bool)
	*/
	function existsEvent ($eventName) {
		return array_key_exists ($eventName, $this->_eventsList);
	}
	
	/**
	 * Returns an event.
	 *
	 * @param $eventName (string)
	 * @public
	 * @return (object event)
	*/
	function getEvent ($eventName) {
		if ($this->existsEvent ($eventName)) {
			return $this->_eventsList[$eventName];
		} else {
			return "ERROR_EVENTMANAGER_EVENT_DOESNT_EXISTS $eventName";
		}
	}
}

?>