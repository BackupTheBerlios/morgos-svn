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

include_once ('interface/smarty/libs/Smarty.class.php');

class SmartyTable {
	var $_smarty;
	var $_headers;
	var $_name;
	var $_data;
	var $_orderHeader;
	var $_orderDirection;

	/**
	 * The constructor.
	 *
	 * @param $name (string)
	 * @param $headers (string array)
	 * @param $data (assoc array array)
	 * @param &$smarty (smarty)
	*/
	function SmartyTable ($name, $headers, $data, &$smarty) {
		$this->_headers = array ();
		foreach ($headers as $header) {
			$this->_headers[$header] = $header;
		}
		$this->_data = $data;
		$this->_name = $name;
		$this->_smarty = &$smarty;
		$this->setSortOrder ($headers[0], SORT_ASC);
	}

	/**
	 * Generates the output. It can contain smarty code
	 *
	 * @return (string)
	*/
	function generateOutput () {
		$this->_smarty->config_load ('table.conf');
		$config = $this->_smarty->get_config_vars ();
		$this->sortData ();
		
		$out = $config['tableStart'];

		$headerrow = $config['headerRow'];
		foreach ($this->_headers as $key=>$header) {
			if ($key == $this->_orderHeader) {
				$nheader = $config['headerCurrentElement'];
			} else {
				$nheader = $config['headerElement'];
			}
			$nheader = str_replace ('HEADER', $header, $nheader);
			$headers .= $nheader;
		}
		$headerrow = str_replace ('HEADERS', $headers, $headerrow);
		$out .= $headerrow;
		
		foreach ($this->_data as $value) {
			$row = $config['dataRow'];
			$data = '';
			foreach ($this->_headers as $key=>$v) {
				$data .= $config['dataElement'];
				$data = str_replace ('VALUE', $value[$key],$data); 
			}
			$row = str_replace ('DATA', $data, $row);
			$out .= $row;
		}
		$out .= $config['tableEnd'];
		return $out;
	}
	
	/**
	 * Sets how to order the table
	 *
	 * @param $headerName (string) the header to sort
	 * @param $orderDirection (int) how to order (ascending or descending)
	*/
	function setSortOrder ($headerName, $orderDirection) {
		$this->_orderHeader = $headerName;
		$this->_orderDirection = $orderDirection; 
	}
	
	/**
	 * Sets a custom header
	 *
	 * @param $header (string)
	 * @param $customContent (string)
	*/
	function setCustomHeader ($header, $customContent) {
		$this->_headers[$header] = $customContent;
	}
	
	/**
	 * Returns the name
	 * @return (string)
	*/
	function getName () {return $this->_name;}
	
	/**
	 * Sorts the data
	 * @protected
	*/
	function sortData () {
		foreach ($this->_headers as $header=>$v) {
			$$header = array ();
		}	
	
		foreach ($this->_data as $key=>$assoc) {
			foreach ($this->_headers as $headerkey=>$v) {
				${$headerkey}[$key] = $assoc[$headerkey];
			}
		}
		$headerName = $this->_orderHeader;
		//die ($$headerName);
		/*echo count ($this->_data);
		echo count ($$headerName);*/
		array_multisort ($$headerName, $this->_orderDirection, $this->_data); 
	}
}

/**
 * A class that extends the functionality for smarty.
 *
 * @ingroup interface
 * @since 0.2
 * @author Nathan Samson
*/
class ExtendedSmarty extends Smarty {
	/**
	 * The constructor.
	*/
	function ExtendedSmarty () {
		parent::Smarty ();
		$this->template_dir = array ();
		$this->register_block ('table', array (&$this, 'table'));
		$this->register_block ('table_custom_header', 
				array (&$this, 'table_custom_header'));
	}	
	
	/**
	 * Adds something to a value, put it after current value.
	 *
	 * @param $varName (string)
	 * @param $extraValue (mixed)
	 * @public
	*/
	function appendTo ($varName, $extraValue) {
		$this->assign ($varName, $this->get_template_vars ($varName).$extraValue);
	}
	
	/**
	 * Prepends something to a value, put it before current value.
	 *
	 * @param $varName (string)
	 * @param $extraValue (mixed)
	 * @public
	*/
	function prependTo ($varName, $extraValue) {
		$this->assign ($varName, $extraValue.$this->get_template_vars ($varName));
	}
	
	/**
	 * defines a table
	 * @protected
	 * @param $params (string array)
	 * @param &$smarty (this object)
	*/
	function table ($params, $content, &$smarty, &$repeat) {
		if ($repeat) {
			$headers = explode (';', $params['headers']);
			$table = new SmartyTable ($params['name'], $headers, 
				$params['data'], $this);
			$orderDirKey = 'orderTable_'.$table->getName ().'_orderDir';
			$orderColKey = 'orderTable_'.$table->getName ().'_orderColumn';
			if (array_key_exists ($orderColKey, $_GET)) {
				$orderCol = $_GET[$orderColKey];
				$orderDir = SORT_ASC;
				if (array_key_exists ($orderDirKey, $_GET)) {
					if ($_GET[$orderDirKey] == 'DESC') {
						$orderDir = SORT_DESC;
					} else {
						$orderDir = SORT_ASC;
					}
				}
				$table->setSortOrder ($orderCol, $orderDir);
			}
		
			$this->_currentTable = $table;
		} else {
			$out = $this->_currentTable->generateOutput ();
			$this->_currentTable = null;
			return $out;
		}
	}
	
	/**
	 * Defines a custom header for the current
	 *
	 * @param $params (string array)
	 * @param $content
	 * @param &$smarty
	 * @param &$repeat 
	*/
	function table_custom_header ($params, $content, &$smarty, &$repeat) {
		if (! $this->_currentTable) {	
			$smarty->trigger_error ('Error: table_custom_header should be placed inside a table');
		}
		if (! $repeat) {
			$this->_currentTable->setCustomHeader ($params['header'], $content);
		}
	}
}

?>