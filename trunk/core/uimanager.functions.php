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
// MorgOS defined functions
$this->functions['form'] = array ('name' => 'FORM', 'params' => array ('ACTION', 'METHOD', 'EXTRA'));
$this->functions['closeform'] = array ('name' => 'CLOSEFORM', 'params' => array ());
$this->functions['input'] = array ('name' => 'INPUT', 'params' => array ('TYPE', 'NAME', 'VALUE', 'EXTRA'));
$this->functions['select'] = array ('name' => 'SELECT', 'params' => array ('NAME', 'EXTRA'));
$this->functions['closeselect'] = array ('name' => 'CLOSESELECT', 'params' => array ());
$this->functions['option'] = array ('name' => 'OPTION', 'params' => array ('ANOPTION'));
$this->functions['admin_database_type_option'] = array ('name' => 'ADMIN_DATABASE_TYPE_OPTION', 'params' => array ('TYPE'));
$this->functions['admin_database_type_option_selected'] = array ('name' => 'ADMIN_DATABASE_TYPE_OPTION_SELECTED', 'params' => array ('TYPE'));
$this->functions['admin_navigation_open'] = array ('name' => 'ADMIN_NAVIGATION_OPEN', 'params' => array ());
$this->functions['admin_navigation_close'] = array ('name' => 'ADMIN_NAVIGATION_CLOSE', 'params' => array ());
$this->functions['admin_navigation_item'] = array ('name' => 'ADMIN_NAVIGATION_ITEM', 'params' => array ('TEXT', 'LINK'));
$this->functions['navigation_open'] = array ('name' => 'NAVIGATION_OPEN', 'params' => array ());
$this->functions['navigation_close'] = array ('name' => 'NAVIGATION_CLOSE', 'params' => array ());
$this->functions['navigation_item'] = array ('name' => 'NAVIGATION_ITEM', 'params' => array ('TEXT', 'LINK'));
$this->functions['admin_page_item'] = array ('name' => 'ADMIN_PAGE_ITEM', 'params' => array ('NAME', 'AUTHORIZED_ONLY', 'LANGUAGE', 'LINK'));
$this->functions['admin_page_form_needauthorize'] = array ('name' => 'ADMIN_PAGE_FORM_NEEDAUTHORIZE', 'params' => array ('NAME'));
$this->functions['admin_page_form_noneedauthorize'] = array ('name' => 'ADMIN_PAGE_FORM_NONEEDAUTHORIZE', 'params' => array ('NAME', 'AUTHORIZED_ONLY', 'LANGUAGE', 'LINK'));
?>
