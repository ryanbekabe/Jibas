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
require_once('include/errorhandler.php');
require_once('include/sessionchecker.php');
require_once('include/common.php');
require_once('include/config.php');
require_once('include/rupiah.php');
require_once('include/db_functions.php');
require_once('include/sessioninfo.php');
require_once('library/jurnal.php');

$idkategori = $_REQUEST['idkategori'];
$idpenerimaan = (int)$_REQUEST['idpenerimaan'];
$replid = (int)$_REQUEST['replid'];
$idtahunbuku = (int)$_REQUEST['idtahunbuku'];
$errmsg = $_REQUEST['errmsg'];

OpenDb();
if (1 == (int)$_REQUEST['issubmit']) 
{	
	$jbayar = UnformatRupiah($_REQUEST['besar']);	
	$tbayar = $_REQUEST['tbayar'];
	$tbayar = MySqlDateFormat($tbayar);
	$kbayar = $_REQUEST['keterangan'];
	$kbayar = urlencode($kbayar);
	$petugas = getUserName();
	
	//Ambil nama penerimaan
	$namapenerimaan = "";
	$rekkas = "";
	$rekpendapatan = "";
	$rekpiutang = "";
	$sql = "SELECT nama, rekkas, rekpendapatan, rekpiutang FROM datapenerimaan WHERE replid=$idpenerimaan";
	$result = QueryDb($sql);
	if (mysql_num_rows($result) == 0) {
		//CloseDb();
		trigger_error("Tidak ditemukan data penerimaan!", E_USER_ERROR);
	} else {
		$row = mysql_fetch_row($result);
		$namapenerimaan = $row[0];
		$rekkas = $row[1];
		$rekpendapatan = $row[2];
		$rekpiutang = $row[3];
	}
	
	//Ambil nama siswa
	$namasiswa = "";
	$sql = "SELECT nama, nopendaftaran FROM jbsakad.calonsiswa WHERE replid=$replid";
	$result = QueryDb($sql);
	if (mysql_num_rows($result) == 0) {
		//CloseDb();
		trigger_error("Tidak ditemukan data calon siswa!", E_USER_ERROR);
	} else {
		$row = mysql_fetch_row($result);
		$namasiswa = $row[0];
		$no = $row[1];
	}
	
	//Ambil awalan dan cacah tahunbuku untuk bikin nokas;
	$sql = "SELECT awalan, cacah FROM tahunbuku WHERE replid = $idtahunbuku";
	$result = QueryDb($sql);
	if (mysql_num_rows($result) == 0) {
		//CloseDb();
		trigger_error("Tidak ditemukan data tahun buku!", E_USER_ERROR);
	} else {
		$row = mysql_fetch_row($result);
		$awalan = $row[0];
		$cacah = $row[1];
	}
	$cacah += 1;
	$nokas = $awalan . rpad($cacah, "0", 6);
	
	//Begin Database Transaction
	BeginTrans();

	//Simpan ke jurnal
	$ketjurnal = "Pembayaran $namapenerimaan tanggal $_REQUEST[tbayar] calon siswa $namasiswa ($no)";
	$idjurnal = 0;
	$success = SimpanJurnal($idtahunbuku, $tbayar, $ketjurnal, $nokas, "", $petugas, "penerimaaniurancalon", $idjurnal);
	
	//Simpan ke jurnaldetail
	if ($success) $success = SimpanDetailJurnal($idjurnal, "D", $rekkas, $jbayar);
	if ($success) $success = SimpanDetailJurnal($idjurnal, "K", $rekpendapatan, $jbayar);
	
	//increment cacah di tahunbuku
	$sql = "UPDATE tahunbuku SET cacah=cacah+1 WHERE replid=$idtahunbuku";
	QueryDbTrans($sql, $success);
	
	$sql = "INSERT INTO penerimaaniurancalon SET idpenerimaan=$idpenerimaan, idcalon=$replid, idjurnal=$idjurnal, jumlah=$jbayar, tanggal='$tbayar', keterangan='$kbayar', petugas='$petugas'";
	QueryDbTrans($sql, $success);
	
	if ($success) {	
		CommitTrans();
	} else {		
		RollbackTrans();
	}
	
	CloseDb();
	header("Location: pembayaran_iurancalon.php?idkategori=$idkategori&idpenerimaan=$idpenerimaan&replid=$replid&idtahunbuku=$idtahunbuku");
	exit();
}

//Muncul pertama kali

$sql = "SELECT c.nopendaftaran, c.nama, c.telponsiswa as telpon, c.hpsiswa as hp, k.kelompok, c.alamatsiswa as alamattinggal, p.proses FROM jbsakad.calonsiswa c, jbsakad.kelompokcalonsiswa k, jbsakad.prosespenerimaansiswa p WHERE c.idkelompok = k.replid AND c.idproses = p.replid AND c.replid = $replid";
//echo  $sql;
$result = QueryDb($sql);
if (mysql_num_rows($result) == 0) {
	CloseDb();
	//echo  "Masuk kesini";
	exit();
} else {
	$row = mysql_fetch_array($result);	
	$no = $row['nopendaftaran'];
	$nama = $row['nama'];
	$telpon = $row['telpon'];
	$hp = $row['hp'];
	$namakelompok = $row['kelompok'];
	$namaproses = $row['proses'];
	$alamattinggal = $row['alamattinggal'];
}
	
$sql = "SELECT nama FROM datapenerimaan WHERE replid = $idpenerimaan";
$result = QueryDb($sql);
$row = mysql_fetch_row($result);
$namapenerimaan = $row[0];


$tanggal = date('d-m-Y');
if (isset($_REQUEST['tbayar']))
	$tanggal = $_REQUEST['tbayar'];
$keterangan = "";
if (isset($_REQUEST['keterangan']))
	$keterangan = $_REQUEST['keterangan'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" type="text/css" href="style/style.css">
<link rel="stylesheet" type="text/css" href="style/tooltips.css">
<link rel="stylesheet" type="text/css" href="style/calendar-green.css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Pembayaran Iuran Calon Siswa</title>
<script type="text/javascript" src="script/calendar.js"></script>
<script type="text/javascript" src="script/lang/calendar-en.js"></script>
<script type="text/javascript" src="script/calendar-setup.js"></script>
<script language="javascript" src="script/rupiah.js"></script>
<script language="javascript" src="script/validasi.js"></script>
<script language="javascript" src="script/tables.js"></script>
<script language="javascript" src="script/tooltips.js" ></script>
<script language="javascript" src="script/tools.js" ></script>
<script src="script/SpryValidationTextField.js" type="text/javascript"></script>
<link href="script/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<script src="script/SpryValidationTextarea.js" type="text/javascript"></script>
<link href="script/SpryValidationTextarea.css" rel="stylesheet" type="text/css" />
<script language="javascript">
var win = null;
function newWindow(mypage,myname,w,h,features) {
      var winl = (screen.width-w)/2;
      var wint = (screen.height-h)/2;
      if (winl < 0) winl = 0;
      if (wint < 0) wint = 0;
      var settings = 'height=' + h + ',';
      settings += 'width=' + w + ',';
      settings += 'top=' + wint + ',';
      settings += 'left=' + winl + ',';
      settings += features;
      win = window.open(mypage,myname,settings);
      win.window.focus();
}

function val2()
{
	if (confirm('Data sudah benar?'))
		return true;
	else 
		return false;
}

function ValidateSubmit() 
{
	var isok = 	validateEmptyText('besar','Jumlah Pembayaran') &&
		   		validasiAngka() &&
		   		validateEmptyText('tbayar','Tanggal Pembayaran') && 
		   		validateMaxText('keterangan', 255, 'Keterangan Pembayaran');
				
	document.getElementById('issubmit').value = isok ? 1 : 0;
	if (isok)
		document.main.submit();
	else
		document.getElementById('simpan').disabled = false;
}

function salinangka(){	
	var angka = document.getElementById("besar").value;
	document.getElementById("angkabesar").value = angka;
}


function validasiAngka() {
	var angka = document.getElementById("angkabesar").value;
	if(isNaN(angka)) {
		alert ('Jumlah pembayaran harus berupa bilangan!');
		document.getElementById('besar').value = "";
		document.getElementById('besar').focus();
		return false;
	}
	else if (angka <= 0)
	{
		alert ('Jumlah pembayaran harus positif!');
		document.getElementById('besar').focus();
		return false;
	}
	return true;
}

function cetakkuitansi(id) {
	newWindow('kuitansiiuran.php?id='+id+'&status=calon','','750','850','resizable=1,scrollbars=1,status=0,toolbar=0');
}

function editpembayaran(id) {
	newWindow('pembayaraniurancalon_edit.php?idpembayaran='+id, 'EditPembayaran','425','392','resizable=1,scrollbars=1,status=0,toolbar=0');
}

function refresh() {
	var idkategori = document.getElementById('idkategori').value;
	var idpenerimaan = document.getElementById('idpenerimaan').value;
	var replid = document.getElementById('replid').value;
	var idtahunbuku = document.getElementById('idtahunbuku').value;
	
	var addr = "pembayaran_iurancalon.php?idkategori="+idkategori+"&idpenerimaan="+idpenerimaan+"&replid="+replid+"&idtahunbuku="+idtahunbuku;
	document.location.href = addr;
}

function cetak() {
	var addr = "pembayaraniurancalon_cetak.php?idkategori=<?=$idkategori ?>&idpenerimaan=<?=$idpenerimaan ?>&replid=<?=$replid ?>&idtahunbuku=<?=$idtahunbuku ?>"
	newWindow(addr, 'CetakPembayaranIuranCalonSiswa','790','630','resizable=1,scrollbars=1,status=0,toolbar=0');
}

function focusNext(elemName, evt) {
    evt = (evt) ? evt : event;
    var charCode = (evt.charCode) ? evt.charCode :
        ((evt.which) ? evt.which : evt.keyCode);
    if (charCode == 13) {
		document.getElementById(elemName).focus();
        return false;
    }
    return true;
}
</script>

</head>

<body topmargin="0" leftmargin="0" onload="document.getElementById('besar').focus();">
<form name="main" id="main" method="post">
<input type="hidden" name="issubmit" id="issubmit" value="0" />
<input type="hidden" name="idkategori" id="idkategori" value="<?=$idkategori ?>" />
<input type="hidden" name="idpenerimaan" id="idpenerimaan" value="<?=$idpenerimaan ?>" />
<input type="hidden" name="replid" id="replid" value="<?=$replid ?>" />
<input type="hidden" name="idtahunbuku" id="idtahunbuku" value="<?=$idtahunbuku ?>" />
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td valign="top" background="" style="background-repeat:no-repeat; background-attachment:fixed">
    <table width="100%" border="0" height="100%" cellspacing="2" cellpadding="2">
   	<tr>
    	<td colspan="2">
        <font size="5" color="#990000"><strong><?=$namapenerimaan ?></strong></font><p></td>
   	</tr>
    <tr>
    	<td width="265" valign="top">        	
			<fieldset style="background:url(images/bttable400.png);height:240px">
            <legend></legend>
            <table border="0" cellpadding="2" cellspacing="2" align="center">
                    
            <tr height="25">
                <td colspan="3" class="header" align="center">Iuran <?=$namapenerimaan?></td>
            </tr>
            <tr>
                <td width="25%"><strong>Pembayaran</strong></td>
                <td colspan="2"><input type="text" readonly="readonly" size="20" value="<?=$namapenerimaan?>" style="background-color:#CCCC99" /></td>
            </tr>
            <tr>
                <td><strong>Jumlah</strong></td>
                <td colspan="2"><input type="text" name="besar" id="besar" size="20" value="<?=FormatRupiah($besar) ?>" onblur="formatRupiah('besar')" onfocus="unformatRupiah('besar')" onKeyPress="return focusNext('keterangan', event)" <?=$dis?> onkeyup="salinangka()" />
                <input type="hidden" name="angkabesar" id="angkabesar" value="<?=$besar ?>" />
                </td>
            </tr>
            <tr>
                <td><strong>Tanggal</strong></td>
                <td>
                <input type="text" name="tbayar" id="tbayar" readonly size="15" value="<?=$tanggal ?>" onKeyPress="return focusNext('keterangan', event)" onClick="Calendar.setup()" style="background-color:#CCCC99"> </td>
                <td width="60%">
                <img src="images/calendar.jpg" name="tabel" border="0" id="btntanggal" onMouseOver="showhint('Buka kalendar!', this, event, '100px')"/>
                </td>        
            </tr>
            <tr>
                <td valign="top">Keterangan</td>
            </tr>
            <tr>
                <td colspan="3"><textarea id="keterangan" name="keterangan" rows="3" cols="35" onKeyPress="return focusNext('simpan', event)" <?=$dis?> style="width:225px; height:50px"><?=$keterangan ?></textarea>
                </td>
            </tr> 
            <tr>
                <td colspan="3" align="center" height="30">
                <input type="button" name="simpan" id="simpan" class="but" value="Simpan" value="1" onclick="this.disabled = true; ValidateSubmit();" style="width:100px"/>
                </td>
            </tr>
            </table>
            </fieldset>            
        </td>
        <td valign="top">
			
            <fieldset style="background:url(images/bttable400.png);height:240px">
            <legend></legend>
            <table border="0" width="100%" cellpadding="2" cellspacing="2">
            <tr height="25">
                <td colspan="4" class="header" align="center">Data Calon Siswa</td>
            </tr>
            <tr valign="top">                    
                <td width="5%"><strong>Pendaftaran</strong></td>
                <td><strong>:</strong></td>
               	<td><strong><?=$no ?></strong> </td>
                <td rowspan="5" width="25%">
                <img src='<?="library/gambar.php?replid=".$replid."&table=jbsakad.calonsiswa";?>' width='100' height='100'></td>
            </tr>
            <tr>
                <td valign="top"><strong>Nama</strong></td>
                <td valign="top"><strong>:</strong></td> 
				<td><strong><?=$nama ?></strong></td>
            </tr>
            <tr>
                <td valign="top"><strong>Proses</strong></td>
                <td valign="top"><strong>:</strong></td>
                <td><strong><?=$namaproses ?></strong></td>
            </tr>
            <tr>
                <td valign="top"><strong>Kelompok</strong></td>
                <td valign="top"><strong>:</strong></td>
                <td><strong><?=$namakelompok ?></strong></td>
            </tr>
            <tr>
                <td><strong>HP</strong></td>
                <td><strong>:</strong></td>
                <td><strong><?=$hp ?></strong></td>
            </tr>
            <tr>
                <td><strong>Telepon</strong></td>
                 <td><strong>:</strong></td>
                <td><strong><?=$telpon ?></strong></td>
            </tr>
            
            <tr>
                <td valign="top"><strong>Alamat</strong></td>
                <td valign="top"><strong>:</strong></td>
                <td colspan="2" valign="top" height="76"><strong>
                  <?=$alamattinggal ?>
                </strong></td>
            </tr>
            <!--<tr>
                <td>&nbsp;</td>
            </tr>           -->
            
            </table>            
            </fieldset>
            
		</td>
  	</tr>
<?  
	$sql = "SELECT p.replid AS id, j.nokas, date_format(p.tanggal, '%d-%b-%Y') as tanggal, p.keterangan, p.jumlah, p.petugas 
	          FROM penerimaaniurancalon p, jurnal j 
				WHERE j.replid = p.idjurnal AND j.idtahunbuku = $idtahunbuku 
				  AND p.idpenerimaan = $idpenerimaan AND p.idcalon = $replid 
			ORDER BY p.tanggal, p.replid";
	
	$result = QueryDb($sql);    
	if (mysql_num_rows($result) > 0) {
?>
    <tr>
        <td align="center" colspan="2">
            <fieldset>
            <legend></legend>
            <table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
        	<tr>
                <td align="right">
                <a href="#" onClick="document.location.reload()"><img src="images/ico/refresh.png" border="0" onMouseOver="showhint('Refresh!', this, event, '50px')"/>&nbsp;Refresh</a>&nbsp;
                <a href="JavaScript:cetak()"><img src="images/ico/print.png" border="0" onMouseOver="showhint('Cetak!', this, event, '50px')"/>&nbsp;Cetak</a>&nbsp;
                </td>
            </tr>
            </table>
            <br />
            <table class="tab" id="table" border="0" style="border-collapse:collapse" width="100%" align="center">
            <tr height="30" align="center">
                <td class="header" width="5%">No</td>
                <td class="header" width="20%">No. Jurnal/Tgl</td>
                <td class="header" width="21%">Jumlah</td>
                <td class="header" width="*">Keterangan</td>
                <td class="header" width="12%">Petugas</td>
                <td class="header">&nbsp;</td>
            </tr>
            <? 
          
            $cnt = 0;
            $total = 0;
            while ($row = mysql_fetch_array($result)) {
                $total += $row['jumlah'];
            ?>
            <tr height="25">
                <td align="center"><?=++$cnt?></td>
                <td align="center"><?="<strong>" . $row['nokas'] . "</strong><br><i>" . $row['tanggal']?></i></td>
                <td align="right"><?=FormatRupiah($row['jumlah'])?></td>
                <td align="left"><?=$row['keterangan'] ?></td>
                <td align="center"><?=$row['petugas'] ?></td>
                <td align="center">
                <a href="javascript:cetakkuitansi(<?=$row['id'] ?>)" ><img src="images/ico/print.png" border="0" onMouseOver="showhint('Cetak Kuitansi Pembayaran!', this, event, '100px')"/></a>&nbsp;
            <?  if (getLevel() != 2) { ?>    
                <a href="javascript:editpembayaran(<?=$row['id'] ?>)"><img src="images/ico/ubah.png" border="0" onMouseOver="showhint('Ubah Pembayaran Cicilan!', this, event, '120px')" /></a>
           	<?	} ?>	                 
                </td>
            </tr>
            <?
            }
            ?>
            <tr height="35">
                <td bgcolor="#996600" colspan="2" align="center"><font color="#FFFFFF"><strong>T O T A L</strong></font></td>
                <td bgcolor="#996600" align="right"><font color="#FFFFFF">
                <strong><?=FormatRupiah($total); ?></strong></font></td>
                <td bgcolor="#996600" colspan="3">&nbsp;</td>
            </tr>
            </table>
            <script language='JavaScript'>
            Tables('table', 1, 0);
            </script>
           	</fieldset>
            
		</td>
    </tr>
<? } ?>
	</table>
<!-- EOF CONTENT -->
</td></tr>
</table>
</form>
</body>
</html>
<script language="javascript">
	 Calendar.setup(
    {
      //inputField  : "tanggalshow","tanggal"
	  inputField  : "tbayar",         // ID of the input field
      ifFormat    : "%d-%m-%Y",    // the date format
      button      : "btntanggal"       // ID of the button
    }
   );
    Calendar.setup(
    {
      inputField  : "tbayar",        // ID of the input field
      ifFormat    : "%d-%m-%Y",    // the date format	  
	  button      : "tbayar"       // ID of the button
    }
  );
	var sprytextfield2 = new Spry.Widget.ValidationTextField("besar");
	var sprytextarea1 = new Spry.Widget.ValidationTextarea("keterangan");
</script>