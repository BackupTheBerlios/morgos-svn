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

	function testTableOrderedDesc () {
		$users = array ();
		$users[] = array ('ID'=>1, 'Name'=>'User1', 'Comment'=>'First Comment.');
		$users[] = array ('ID'=>3, 'Name'=>'XYZ', 'Comment'=>'GHI Comment.');
		$users[] = array ('ID'=>2, 'Name'=>'Another User', 'Comment'=>'Last Comment.');
		$this->smarty->assign ('UserData', $users);
		global $_GET;
		$_GET['orderTable_testTable_orderDir'] = 'DESC';
		$_GET['orderTable_testTable_orderColumn'] = 'ID';
		$result = $this->smarty->fetch ('table.tpl');
		$exp = file_get_contents ('interface/tests/smarty/table_desc_expected.tpl');	
		$this->assertEquals ($exp, $result);
	}
	
	function testTableOrderedAscName () {
		$users = array ();
		$users[] = array ('ID'=>1, 'Name'=>'User1', 'Comment'=>'First Comment.');
		$users[] = array ('ID'=>3, 'Name'=>'XYZ', 'Comment'=>'GHI Comment.');
		$users[] = array ('ID'=>2, 'Name'=>'Another User', 'Comment'=>'Last Comment.');
		$this->smarty->assign ('UserData', $users);
		global $_GET;
		$_GET['orderTable_testTable_orderDir'] = 'ASC';
		$_GET['orderTable_testTable_orderColumn'] = 'Name';
		$result = $this->smarty->fetch ('table.tpl');
		$exp = file_get_contents ('interface/tests/smarty/table_asc_name_expected.tpl');	
		$this->assertEquals ($exp, $result);
		global $_GET;
		$_GET = array ();
	}
	
	function testCustomHeader () {
		$users = array ();
		$users[] = array ('ID'=>1, 'Name'=>'User1', 'Comment'=>'First Comment.');
		$users[] = array ('ID'=>3, 'Name'=>'XYZ', 'Comment'=>'GHI Comment.');
		$users[] = array ('ID'=>2, 'Name'=>'Another User', 'Comment'=>'Last Comment.');
		$this->smarty->assign ('UserData', $users);
		$result = $this->smarty->fetch ('table_custom_headers.tpl');
		$exp = file_get_contents ('interface/tests/smarty/table_custom_header_exp.tpl');	
		$this->assertEquals ($exp, $result);
	}
	
	function testCustomData () {
		$users = array ();
		$users[] = array ('ID'=>1, 'Name'=>'User1', 'LastMessageID'=>1, 'LastMessageText'=>1);
		$users[] = array ('ID'=>3, 'Name'=>'XYZ', 'LastMessageID'=>0, 'LastMessageText'=>0);
		$users[] = array ('ID'=>2, 'Name'=>'Another User', 'LastMessageID'=>8, 'LastMessageText'=>8);
		$this->smarty->assign ('UserData', $users);
		$result = $this->smarty->fetch ('table_data.tpl');
		$exp = file_get_contents ('interface/tests/smarty/table_data_exp.tpl');	
		$this->assertEquals ($exp, $result);
	}
}

?>