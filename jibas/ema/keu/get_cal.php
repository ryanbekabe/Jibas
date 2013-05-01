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
require_once('../inc/config.php');
require_once('../inc/sessionchecker.php');
require_once('../inc/getheader.php');
require_once('../inc/common.php');
require_once('../inc/db_functions.php');
OpenDb();
$kelompok=$_REQUEST[kelompok];
?>
<link href="../style/style.css" rel="stylesheet" type="text/css" />
<table width="100%" border="1" class="tab">
  <tr>
    <td height="25" align="center" class="header">No.&nbsp;Pendaftaran</td>
    <td height="25" align="center" class="header">Nama</td>
    <td height="25" align="center" class="header">&nbsp;</td>
  </tr>
  <?
  $sql = "SELECT * FROM calonsiswa WHERE idkelompok='$kelompok' ORDER BY nama";
  $result = QueryDb($sql);
  $num = @mysql_num_rows($result);
  if ($num>0){
  while ($row = @mysql_fetch_array($result)){
  ?>
  <tr>
    <td height="20" align="center"><?=$row[nopendaftaran]?></td>
    <td height="20"><?=$row[nama]?></td>
    <td height="20" align="center"><input type="button" value=" > " class="cmbfrm2" onClick="pilihcalon('<?=$row[replid]?>')" /></td>
  </tr>
  <? } ?>
  <? } else { ?>
  <tr>
    <td height="20" colspan="3" align="center" class="nodata">Tidak ada data</td>
  </tr>
  <? } ?>
</table>