<?
/**[N]**
 * JIBAS Education Community
 * Jaringan Informasi Bersama Antar Sekolah
 * 
 * @version: 3.0 (January 09, 2013)
 * @notes: JIBAS Education Community will be managed by Yayasan Indonesia Membaca (http://www.indonesiamembaca.net)
 * 
 * Copyright (C) 2009 Yayasan Indonesia Membaca (http://www.indonesiamembaca.net)
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 **[N]**/ ?>
<?
require_once('../include/common.php');
require_once('../include/config.php');
require_once('../include/db_functions.php');
$departemen=$_REQUEST['departemen'];
?>
		<select name="dep_asal" id="dep_asal"  onKeyPress="return focusNext('sekolah', event)" onChange="change_departemen()">
        <option value="">[Departemen]</option>
      	<? // Olah untuk combo sekolah
		OpenDb();
		$sql_dep_asal="SELECT DISTINCT departemen FROM asalsekolah ORDER BY departemen";
		$result_dep_asal=QueryDB($sql_dep_asal);
		while ($row_dep_asal = mysql_fetch_array($result_dep_asal)) {
			if ($departemen=="")
				$departemen=$row_dep_asal['departemen'];
		?>
       <option value="<?=$row_dep_asal['departemen']?>" <?=StringIsSelected($row_dep_asal['departemen'],$departemen)?>>
        <?=$row_dep_asal['departemen']?>
        </option>
      <?
    	} 
		CloseDb();
		// Akhir Olah Data sekolah
		?>
    	</select>