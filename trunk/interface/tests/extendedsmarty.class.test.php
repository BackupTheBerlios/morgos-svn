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

include_once ('interface/extendedsmarty.class.php');

class TestSmarty extends TestCase {

	function setUp () {
		$this->smarty = new ExtendedSmarty ();
		$this->smarty->compile_dir = 'interface/tests/smarty_c';
		$this->smarty->template_dir = 'interface/tests/smarty';
		$this->smarty->config_dir = 'interface/tests/smarty';
	}
	
	function testNormalTable () {
		$users = array ();
		$users[] = array ('ID'=>1, 'Name'=>'User1', 'Comment'=>'First Comment.');
		$users[] = array ('ID'=>3, 'Name'=>'XYZ', 'Comment'=>'GHI Comment.');
		$users[] = array ('ID'=>2, 'Name'=>'Another User', 'Comment'=>'Last Comment.');
		$this->smarty->assign ('UserData', $users);
		$result = $this->smarty->fetch ('table.tpl');
		$exp = file_get_contents ('interface/tests/smarty/table_expected.tpl');	
		$this->assertEquals ($exp, $result);
	}

}

?>