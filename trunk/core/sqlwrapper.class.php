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
/** \file sqlwrapper.class.php
 * File that take care of the classes for the SQL wrapper
 *
 * @ingroup core database
 * @since 0.2
 * @since 0.3 splitted from databasemanager.functions.php
 * @author Nathan Samson
*/

define ('ORDER_ASC', 'ASC');
define ('ORDER_DESC', 'DESC');

/**
 * A class that represents a field in some database
 *
 * @ingroup core database
 * @since 0.2
 * @author Nathan Samson
*/
class dbField {
	var $_name;
	var $_type;
	var $_value;
	var $canBeNull;
	
	function dbField ($name = null, $type = null) {
		$this->canBeNull = false;
		$this->_type = $type;
		$this->_name = $name;
		$this->_value = null;
	}	
	
	function setValue ($newValue) {
		if ($newValue === null) {
			$this->_value = null;			
		} elseif ($this->getNonDBType () == 'string') {
			$this->_value = strval ($newValue);
		} elseif ($this->getNonDBType () == 'int') {
			$this->_value = (int) ($newValue);
		} else {
			$this->_value = $newValue;
		}
	}
	
	function getValue () {
		return $this->_value;
	}
	
	function getNonDBType () {
		if (substr ($this->getType (), 0, 3) == 'int') {
			return 'int';
		} else {
			return 'string';
		}
	}
	
	function getName () {return $this->_name;}
	
	/**
	 * Returns the raw db type of the field
	 *
	 * @return (string) 
	 * @since 0.3
	*/
	function getType () {return $this->_type;}	
	
}

/**
 * A class that represents a generic join field in a table
 * @ingroup core database
 * @since 0.3
 * @author Nathan Samson
*/
class genericJoinField {
	var $_otherTable;
	var $_otherField;
	var $_name;
	var $_dbField;

	function genericJoinField ($name, $otherTable, $otherField, &$dbField) {
		$this->_name = $name;
		$this->_otherTable = $otherTable;
		$this->_otherField = $otherField;
		$this->_dbField = &$dbField;
	}
	
	function getOtherTable () {return $this->_otherTable;}
	function getOtherField () {return $this->_otherField;}
	function getName () {return $this->_name;}
	function getValue () {return $this->_dbField->getValue ();}
}

/**
 * A class that represents a one-to-multiple join field in a table
 * @ingroup core database
 * @since 0.3
 * @author Nathan Samson
*/
class oneToMultipleJoinField extends genericJoinField {
	function oneToMultipleJoinField ($name, $otherTable, $otherField, $dbField) {
		parent::genericJoinField ($name, $otherTable, $otherField, $dbField);
	} 
}

/**
 * A class that represents a multiple-to-one join field in a table
 * @ingroup core database
 * @since 0.3
 * @author Nathan Samson
*/
class mulitpleToOneJoinField extends genericJoinField {
	function mulitpleToOneJoinField ($name, $otherTable, $otherField, $dbField) {
		parent::genericJoinField ($name, $otherTable, $otherField, $dbField);
	} 
}

/**
 * A class that represents a one-to-one join field in a table
 * @ingroup core database
 * @since 0.3
 * @author Nathan Samson
*/
class oneToOneJoinField extends genericJoinField {
	function oneToOneJoinField ($name, $otherTable, $otherField, $dbField) {
		parent::genericJoinField ($name, $otherTable, $otherField, $dbField);
	} 
}

/**
 * A generic class that represents Where clauses in SQL statements
 *
 * @ingroup core databease
 * @since 0.3
 * @author Nathan Samson
*/
class WhereClause {
	var $_operator;
	var $_field;
	var $_value;

	function WhereClause ($field, $value, $operator) {
		$this->_field = $field;
		$this->_value = $value;
		if ($this->isValidOperator ($operator)) {
			$this->_operator = $operator;
		} else {
			$this->_operator = '=';
		}
	}
	
	function getSQLString () {
		return $this->getField().$this->getOperator().'\''.$this->getValue ().'\' ';
	}
	
	function getValue () {
		return (string) $this->_value;
	}
	
	function getField () {
		return $this->_field;
	}
	
	function getOperator () {
		switch ($this->_operator) {
			case 'LIKE':
				return ' LIKE ';
			case 'BETWEEN':
				return ' BETWEEN ';
			default:
				return $this->_operator;	
		}
	}
	
	function isValidOperator ($op) {
		switch ($op) {
			case '=':
			case '>=':
			case '<=':
			case '>' :
			case '<' :
			case '<>':
			case 'LIKE':
			case 'BETWEEN':
				return true;
			default:
				return false;
		}
	}
}

/**
 * A Database table object. All tables that are stored in the database must be derived 
 * from this one.
 * 
 * @ingroup core database
 * @since 0.2
 * @since 0.3 renamed to DBTableObject, previously it was databaseObject
 * @author Nathan Samson
*/
class DBTableObject {
	/**
	 * A mixed array of all basic fields with their values
	 * @private 
	*/
	var $_basicFields;
	/**
	 * A mixed array of all extra fields with their values
	 * @private
	*/
	var $_extraFields;
	/**
	 * The databasemodule object
	 * @protected
	*/
	var $_db;
	/**
	 * The name of the database field for the ID
	 * @private
	*/
	var $_IDName;
	/**
	 * The creator of the object
	 * @private
	*/
	var $_creator;
	/**
	 * The different joins of this table
	 * @since 0.3
	 * @private
	*/
	var $_joins;
	
	/**
	 * The constructor
	 *
	 * @param $db (databaseModule)
	 * @param $basicFields (object option array)
	 * @param $tableName (string)
	 * @param $IDName (int)
	 * @param $creator (object) default null
	 * @param $extraFields (object option array) default array ()
	 * @param $joins (object genericJoind array) default array ()
	*/
	function databaseObject (&$db, $basicFields, $tableName, $IDName, &$creator = null, 
			$extraFields = array (), $joins = array ()) {
		$this->_db = &$db;
		
		$this->setIDName ($IDName);
		$this->setCreator ($creator);
		$this->setTableName ($tableName);
		
		$this->setExtraFields ($extraFields);
		$this->setBasicFields ($basicFields);
		$this->setJoins ($joins);
		
		$this->initEmpty ();
	}	
	
	/*Public initters*/	
	
	/**
	 * Initializes the object with an ID
	 *
	 * @param $ID (int)
	 * @public
	*/
	function initFromDatabaseID ($ID) {
		if (! is_numeric ($ID)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__,__LINE__);
		}
		$prefix = $this->_db->getPrefix ();
		$tableName = $this->getTableName ();
		$IDName = $this->getIDName ();
		$sql = "SELECT * FROM $prefix$tableName WHERE $IDName='$ID'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			if ($this->_db->numRows ($q) > 0) {
				$row = $this->_db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->setField ('ID', $row[$this->getIDName ()]);
			} else {
				return new Error ('DATABASEOBJECT_ID_NOT_FOUND');
			}
		} else {
			return $q;
		}
	}
	
	/**
	 * Initializes the object from an array
	 * @warning If a key isn't a valid field, an error will be generated and the object
	 * 		will be inited empty again.
	 *
	 * @param $array (mixed array) The keys are the options.
	 * @public
	*/
	function initFromArray ($array) {
		if (! is_array ($array)) {
			var_dump ($array);
			return new Error ('PAREMTER_WRONG_PARAMETER', $array);
		}
		$pVal = $this->getFieldValue ('ID');
		$this->initEmpty ();
		$allFields = $this->getAllFields ();
		foreach ($allFields as $dbField) {
			$name = $dbField->getName ();
			if (array_key_exists ($name, $array)) {
				$this->setField ($name, $array[$name]);
			} else {
				if ((! $dbField->canBeNull) and ($dbField->getName () != $this->getIDName ())) {
					$this->initEmpty ();
					return new Error ('DATABASEOBJECT_KEY_NOT_EXISTS', $name);
				}
			}
		}
		if ($this->getFieldValue ('ID') !== $pVal) { 
		//	var_dump ($this->getFieldValue ('ID'));
		}
	}
	
	/*Public functions*/
	
	/**
	 * Gets the current value of a field.
	 *
	 * @param $name (string) the name of the field
	 * @public
	 * @return (mixed)
	*/
	function getFieldValue ($name) {
		$field = $this->getField ($name);
		if (! isError ($field)) {
			return $field->getValue ();
		} else {
			return $field;
		}
	}
	
	/**
	 * Returns the field object. 
	 * @param $name (string )
	 * @public
	 * @return (object dbField)
	*/
	function getField ($name) {
		$allFields = $this->getAllFields ();
		if (array_key_exists ($name, $allFields)) {
			return $allFields[$name];
		} elseif ($name == 'ID') {
			return $allFields[$this->getIDName ()];
		} else {
			return new Error ('DATABASEOBJECT_OPTION_DOES_NOT_EXISTS', $name);
		}
	}

	
	/**
	 * Sets the value of an field
	 * @warning all values are converted to a string.
	 *
	 * @param $name (string) the name of the field
	 * @param $value (mixed) the new value
	 * @public
	*/
	function setField ($name, $value) {
		$allExtraFields = $this->getExtraFields ();
		$allBasicFields = $this->getBasicFields ();
		if (array_key_exists ($name, $allExtraFields)) {
			$this->_extraFields[$name]->setValue ($value);
		} elseif (array_key_exists ($name, $allBasicFields)) {
			$this->_basicFields[$name]->setValue ($value);
		} elseif ($name == 'ID') {
			$this->_basicFields[$this->getIDName ()]->setValue ($value);
		} else {
			return new Error ('DATABASEOBJECT_OPTION_DOES_NOT_EXISTS', $name);
		}
	}	
	
	/**
	 * Updates all values from an array.
	 *
	 * @param $array (mixed array) The keys are the fields names, values are the new values.
	 * @public
	*/
	function updateFromArray ($array) {
		$allFields = $this->getAllFields ();
		foreach ($allFields as $option => $value) {
			if (array_key_exists ($option, $array)) {
				$this->setField ($option, $array[$option]);
			}
		}
	}
	
	/**
	 * Checks that the current object is stored into the database.
	 *
	 * @public
	 * @return (bool)
	*/
	function isInDatabase () {
		if ($this->getFieldValue ('ID') < 0) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Insert the object into the database.
	 *	 
	 * @public
	*/
	function addToDatabase () {
		if (! $this->isInDatabase ()) {
			$prefix = $this->_db->getPrefix ();
			$tableName = $this->getTableName ();
			//$IDName = $this->getIDName ();
			$sql = "INSERT into $prefix$tableName (";
			$allFields = $this->getAllFields ();
			foreach ($allFields as $dbField) {				
				$name = $dbField->getName ();
				if ($name != $this->getIDName ()) {
					$sql.= "$name,";
				}
			}
			$sql[strlen ($sql)-1] = ')'; // remove latest , with )			
			$sql .= ' VALUES (';
			foreach ($allFields as $dbField) {
				if ($dbField->getName () != $this->getIDName ()) {
					$value = $this->_db->escapeString ($dbField->getValue ());
					$sql.= "'$value',";
				}
			}
			$sql[strlen ($sql)-1] = ')'; // remove latest , with )	
			$q = $this->_db->query ($sql);			
			if (! isError ($q)) {
				$ID = $this->_db->latestInsertID ($q);
				$this->setField ('ID', $ID);
			} else {
				return $q;
			}
		} else {
			return new Error ('DATABASEOBJECT_ALREADY_IN_DATABASE');
		}
	}

	/**
	 * Removes the object form the database.
	 *
	 * @public
	*/	
	function removeFromDatabase () {
		if ($this->isInDatabase ()) {
			$prefix = $this->_db->getPrefix ();
			$tableName = $this->getTableName ();
			$IDName = $this->getIDName ();
			$ID = $this->getID ();
			if (! is_numeric ($ID)) {
				return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', 
					__FILE__,__LINE__);
			}
			$sql = "DELETE FROM $prefix$tableName WHERE $IDName='$ID'";
			$q = $this->_db->query ($sql);
			if (! isError ($q)) {
				$this->setField ('ID', -1);
			} else {
				return $q;
			}
		} else {
			return new Error ('DATABASEOBJECT_NOT_IN_DATABASE');
		}
	}
	
	/**
	 * Update the data to the database.
	 * @public
	*/
	function updateToDatabase () {
		$updates = '';
		foreach ($this->getAllFields () as $field) {
		 	if (! empty ($updates)) {
		 		$updates .= ', ';
		 	}
			$updates .= $field->getName ();
			$updates .= '=';
			$updates .= '\''.$field->getValue ().'\' '; 
		}
		$fullTableName = $this->getFullTableName (); 
		$IDName = $this->getIDName ();
		$ID = $this->getID ();
		$sql = "UPDATE $fullTableName SET $updates WHERE $IDName='$ID'";
		$a = $this->_db->query ($sql);
		return $a;
	}
	
	/**
	 * @since 0.3 
	*/
	function getAllChildTables ($joinFieldName, $orderField = null, $order = ORDER_ASC, 
			$extraWhere = array (), $fields = '*') {
		$join = $this->getJoin ($joinFieldName);
		if (! isError ($join)) {
			if (is_a ($join, 'oneToMultipleJoinField')) {
				$table = $join->getOtherTable ();
				$otherField = $join->getOtherField ();
				$value = $join->getValue ();
				$sql = "SELECT $fields FROM $table WHERE $otherField='$value'";
				
				if ($extraWhere != array ()) {
					$sql .= " AND ";
					$whereSQLA = array ();
					foreach ($extraWhere as $s) {
						//var_dump ( $s->getSQLString ());
						$whereSQLA[] = $s->getSQLString ();
					}
					$sql .= implode ('AND', $whereSQLA);
				}				
				
				if ($orderField != null) {
					$sql .= " ORDER BY $orderField $order";
				}
				$q = $this->_db->query ($sql);
				if (! isError ($q)) {
					$rows = array ();
					while ($row = $this->_db->fetchArray ($q)) {
						$rows[] = $row;
					}
					return $rows;
				} else {
					return $q;
				}
			} else {
			}
		} else {
			return $join;
		}
	}
	
	/**
	 * @since 0.3 
	*/
	function getAllParentTables ($joinFieldName) {
	}

	/**
	 * @since 0.3 
	*/
	function getSideTable ($joinFieldName) {
		$field = $this->getField ($joinFieldName);
		if (! isError ($field)) {
			if (is_a ($field, 'oneToOneJoinField')) {
				$table = $field->getOtherTable ();
				$otherField = $field->getOtherField ();
				$value = $field->getValue ();
				$sql = "SELECT * FROM $table WHERE $field=$value";
				$q = $this->_db->query ($sql);
				return $this->_db->fetchArray ($q);
			}
		} else {
			return $field;
		}
	}
	
	/**
	 * Gets the ID of the object.
	 *
	 * @return (int)
	 * @public
	*/	
	function getID () {return $this->getFieldValue ('ID');}
	
	/*Protected functions*/
	
	/**
	 * Sets the fieldname of the ID
	 *
	 * @param $name (string) fieldname
	 * @protected
	*/
	function setIDName ($name) {
		$this->_IDName = $name;
	}
	/**
	 * Returns the fieldname of the ID value
	 *
	 * @protected
	 * @return (string)
	*/
	function getIDName () {return $this->_IDName;}
	
	/**
	 * Sets the tablename where the data should be stored (without dbprefix)
	 *
	 * @param $name (string) tablename (without prefix)
	 * @protected
	*/
	function setTableName ($name)	 {
		$this->_tableName = $name;
	}
	
	/**
	 * Returns the tablename where the object is stored (without prefix)
	 *
	 * @protected
	 * @return (string)
	*/
	function getTableName () {return $this->_tableName;}
	
	/**
	 * Returns the full tablename where the object is stored (with prefix)
	 *
	 * @protected
	 * @return (string)
	*/
	function getFullTableName () {return $this->_db->getPrefix () . $this->getTableName ();}
	
	/**
	 * Set the creator of the object.
	 *
	 * @param $creator (object)
	 * @protected
	*/
	function setCreator (&$creator) {
		$this->_creator = &$creator;
	}
	
	/**
	 * Returns the creator of the object
	 *
	 * @protected
	 * @return (object)
	*/
	function getCreator () {return $this->_creator; }
	
	/**
	 * Set all extra fields that should be stored in the db.
	 *
	 * @param $array (object dbField array)
	 * @protected
	*/
	function setExtraFields ($array) {
		$this->_extraFields = array ();
		foreach ($array as $e) {
			$this->_extraFields[$e->getName ()] = $e;
		}
	}
	
	/**
	 * Returns all extra fields
	 *
	 * @protected
	 * @return (mixed array) the keys are the fieldname. Values are the current values.
	*/
	function getExtraFields () {return $this->_extraFields;}
	
	/**
	 * Set all basic fields (without ID) that should be stored in the DB.
	 *
	 * @param $array (object dbField)
	 * @protected
	*/
	function setBasicFields ($array) {
		$this->_basicFields = array ();
		foreach ($array as $e) {
			$this->_basicFields[$e->getName ()] = $e;
		}
		if (! array_key_exists ($this->getIDName (), $this->_basicFields)) {
			$this->_basicFields[$this->getIDName ()] = new dbField ($this->getIDName (), 'int (11)');
		}
	}
	
	/**
	 * Returns all basic fields
	 *
	 * @protected
	 * @return (mixed array) the keys are the fieldnames. Values are the current values.
	*/
	function getBasicFields () {return $this->_basicFields;}
	
	/**
	 * Returns an array of all fields. (without ID)
	 *
	 * @protected
	 * @return (mixed array) the keys are the fieldnames. Values are the current values.
	*/
	function getAllFields () {
		return array_merge ($this->getExtraFields (), $this->getBasicFields ()); 
	}
	
	/**
	 * Set the join fields.
	 *
	 * @param $joins (object genericJoin array) a list of the joins
	 * @protected
	*/
	function setJoins ($joins) {
		$this->_joins = array ();
		foreach ($joins as $j) {
			$this->_joins[$j->getName ()] = $j;
		}
	}
	
	/**
	 * Return a specific join field.
	 *
	 * @param $name (string)
	 * @public
	 * @return (object genericJoin)
	*/
	function getJoin ($name) {
		if (array_key_exists ($name, $this->_joins)) {
			return $this->_joins[$name];
		} else {
			return new Error ('FIELD_JOIN_NOT_FOUND', $this->getTableName (), $name);
		}
	}
	
	/**
	 * Empty initializer.
	 *	 
	 * @protected
	*/	
	function initEmpty () {
		$this->setField ('ID', -1);
		$allFields = $this->getAllFields ();
		foreach ($allFields as $name => $value) {
			if ($name != $this->getIDName ()) {
				$this->setField ($name, null);
			}
		}
	}

}

?>
