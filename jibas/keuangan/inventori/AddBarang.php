<?
/**[N]**
 * JIBAS Road To Community
 * Jaringan Informasi Bersama Antar Sekolah
 * 
 * @version: 2.5.0 (Juni 20, 2011)
 * @notes: JIBAS Education Community will be managed by Yayasan Indonesia Membaca (http://www.indonesiamembaca.net)
 * 
 * Copyright (C) 2009 PT.Galileo Mitra Solusitama (http://www.galileoms.com)
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
require_once('../include/config.php');
require_once('../include/db_functions.php');
require_once('../include/common.php');
require_once('../../akademik/include/imageresizer.php');

OpenDb();
 
$sql = "SELECT MONTH(NOW()), YEAR(NOW()), DAY(NOW())";
$result = QueryDb($sql);
$row = mysql_fetch_row($result);
$now = $row[2]."-".$row[0]."-".$row[1];
$tgl = $now;
if (isset($_REQUEST[tgl]))
	$tgl = $_REQUEST[tgl];
if (isset($_REQUEST[Simpan])){
	$sql = "SELECT kode FROM jbsfina.barang WHERE kode='$_REQUEST[kode]'";
	$result = QueryDb($sql);
	$num = @mysql_num_rows($result);
	if ($num>0){
		?>
		<script language="javascript">
			alert('Kode Barang \'<?=$_REQUEST[kode]?>\' sudah digunakan!');
        </script>
		<?
	} else {
		$foto=$_FILES["foto"];
		$uploadedfile = $foto['tmp_name'];
		$uploadedtypefile = $foto['type'];
		$uploadedsizefile = $foto['size'];
		if (strlen($uploadedfile) != 0)
		{
			$tmp_path = realpath(".") . "/../../temp";
			$tmp_exists = file_exists($tmp_path) && is_dir($tmp_path);
			if (!$tmp_exists)
				mkdir($tmp_path, 0755);
		
			$filename = "$tmp_path/ad-inv-tmp.jpg";
			ResizeImage($foto, 120, 90, 70, $filename);

			$fh = fopen($filename,"r");
			$foto_data = addslashes(fread($fh, filesize($filename)));
			fclose($fh);
			
			$isifoto = ", foto='$foto_data'";
		} else {
			$isifoto="";
		}
		
		$tgl = MySqlDateFormat($_REQUEST[tgl]);
		
		$sql = "INSERT INTO jbsfina.barang SET kode='".trim($_REQUEST[kode])."', nama='".trim($_REQUEST[nama])."', jumlah='".trim($_REQUEST[jumlah])."',kondisi='".addslashes(trim($_REQUEST[kondisi]))."',tglperolehan='$tgl',keterangan='".addslashes(trim($_REQUEST[keterangan]))."',idkelompok='$_REQUEST[idkelompok]',satuan='$_REQUEST[satuan]' $isifoto";
		$result = QueryDb($sql);
		if ($result){
			?>
            <script language="javascript">
				parent.opener.GetFresh();
				window.close();
            </script>
            <?
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Tambah Barang</title>
<link href="../style/style.css" rel="stylesheet" type="text/css" />
<script language="javascript" src="../script/tools.js"></script>
<script language="javascript">
function TakeDate(elementid){
	var addr = "../library/cals.php?elementid="+elementid;
	newWindow(addr, 'CariTanggal','338','216','resizable=0,scrollbars=0,status=0,toolbar=0')
}
function AcceptDate(date,elementid){
	document.getElementById(elementid).value=date;
}
function validate(){
	var kode = document.getElementById('kode').value;
	var nama = document.getElementById('nama').value;
	var jumlah = document.getElementById('jumlah').value;
	var tgl = document.getElementById('tgl').value;
	
	if (kode.length==0){
		alert ('Anda harus mengisikan data untuk Kode Barang!');
		document.getElementById('kode').focus();
		return false;
	} else {
		if (kode.length>20){
			alert ('Kode Barang maksimal 20 karakter!');
			document.getElementById('kode').focus();
			return false;
		}
	}
	
	if (nama.length==0){
		alert ('Anda harus mengisikan data untuk Nama Barang!');
		document.getElementById('nama').focus();
		return false;
	} else {
		if (nama.length>50){
			alert ('Nama Barang maksimal 50 karakter!');
			document.getElementById('nama').focus();
			return false;
		}
	}
	
	if (jumlah.length==0){
		alert ('Anda harus mengisikan nilai untuk Jumlah Barang!');
		document.getElementById('jumlah').focus();
		return false;
	} else {
		if (jumlah.length>10){
			alert ('Jumlah Barang maksimal 10 digit!');
			document.getElementById('jumlah').focus();
			return false;
		}
		if (isNaN(jumlah)){
			alert ('Jumlah Barang harus berupa bilangan!');
			document.getElementById('jumlah').value="";;
			document.getElementById('jumlah').focus();
			return false;
		}
	}
	
	if (tgl.length==0){
		alert ('Anda harus mengisikan data untuk Tanggal Perolehan!');
		document.getElementById('tgl').focus();
		return false;
	} else {
		if (tgl.length>10){
			alert ('Tanggal Perolehan maksimal 10 karakter!');
			document.getElementById('tgl').focus();
			return false;
		}
	}
}
</script>
</head>
<body onLoad="document.getElementById('kode').focus()">
<fieldset style="border:#336699 1px solid; background-color:#eaf4ff" >
<legend style="background-color:#336699; color:#FFFFFF; font-size:10px; font-weight:bold; padding:5px">&nbsp;Tambah&nbsp;Barang&nbsp;</legend>
<form action="AddBarang.php" method="post" enctype="multipart/form-data" onSubmit="return validate()">
<input type="hidden" name="idkelompok" id="idkelompok" value="<?=$_REQUEST[idkelompok]?>" />
<table width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td width="14%" align="right"><strong>Kode&nbsp;Barang</strong></td>
    <td width="86%"><input type="text" id="kode" name="kode" maxlength="20" /></td>
  </tr>
  <tr>
    <td align="right"><strong>Nama&nbsp;Barang</strong></td>
    <td><input type="text" id="nama" name="nama" style="width:95%" maxlength="50" /></td>
  </tr>
  <tr>
    <td align="right"><strong>Jumlah</strong></td>
    <td><input type="text" id="jumlah" name="jumlah" size="5" maxlength="10" />
    &nbsp;Satuan&nbsp;<input type="text" id="satuan" name="satuan" size="10" maxlength="20" value="unit" /></td>
  </tr>
  <tr>
    <td align="right">Kondisi</td>
    <td><textarea name="kondisi" id="kondisi"  style="width:95%"></textarea></td>
  </tr>
  <tr>
    <td align="right"><strong>Tanggal Perolehan</strong></td>
    <td><input type="text" id="tgl" name="tgl" maxlength="10" size="11" value="<?=$tgl?>" />&nbsp;<a href="javascript:TakeDate('tgl')"><img src="../images/ico/calendar.png" border="0" /></a>&nbsp;<em>mm-dd-yyyy</em></td>
  </tr>
  <tr>
    <td align="right">Foto</td>
    <td><input type="file" id="foto" name="foto" /></td>
  </tr>
  <tr>
    <td align="right">Keterangan</td>
    <td><textarea name="keterangan" rows="4" id="keterangan"  style="width:95%"></textarea></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input name="Simpan" type="submit" class="but" value="Simpan" />
    &nbsp;&nbsp;<input name="Batal" type="button" class="but" onClick="window.close()" value="Batal" /></td>
  </tr>
</table>
</form>
</fieldset>
<?
CloseDb();
?>
</body>
</html>