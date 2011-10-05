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
include("../library/class/jpgraph.php");
include("../library/class/jpgraph_pie.php");
include("../library/class/jpgraph_pie3d.php");

$idproses=(int)$_REQUEST['idproses'];
$departemen=$_REQUEST['departemen'];
$dasar = $_REQUEST['dasar'];
$tabel = $_REQUEST['tabel'];
OpenDb();
$i = 0;

if ($dasar == 'Golongan Darah') {
	$row = array('A','0','B','AB','');
	$judul = array(1=>'A','0','B','AB','Tidak ada data');
	$jum = count($row);	
} elseif ($dasar == 'Jenis Kelamin') {
	$row = array('l','p');
	$judul = array(1=>'Laki-laki','Perempuan');
	$jum = count($row);
} elseif ($dasar == 'Kewarganegaraan') {
	$row = array('WNI','WNA');
	$judul = array(1=>'WNI','WNA');
	$jum = count($row);
} elseif ($dasar == 'Status Aktif') {
	$row = array(1,0);
	$judul = array(1 => 'Aktif','Tidak Aktif');
	$jum = count($row);
} elseif ($dasar == 'Kondisi Siswa') {	
	$query = "SELECT $tabel FROM jbsakad.kondisisiswa ORDER BY $tabel ";
	$result = QueryDb($query);
	$jum = @mysql_num_rows($result);
} elseif ($dasar == 'Status Siswa') {	
	$query = "SELECT $tabel FROM jbsakad.statussiswa ORDER BY $tabel ";
	$result = QueryDb($query);
	$jum = @mysql_num_rows($result);
} elseif ($dasar == 'Pekerjaan Ayah' || $dasar == 'Pekerjaan Ibu') {	
	$query = "SELECT pekerjaan FROM jbsumum.jenispekerjaan ORDER BY pekerjaan ";
	$result = QueryDb($query);
	$jum = @mysql_num_rows($result);
} elseif ($dasar == 'Pendidikan Ayah' || $dasar == 'Pendidikan Ibu') {	
	$query = "SELECT pendidikan FROM jbsumum.tingkatpendidikan ORDER BY pendidikan ";
	$result = QueryDb($query);
	$jum = @mysql_num_rows($result);
} elseif ($dasar == 'Penghasilan Orang Tua') {		
	$batas = array(0,1000000,2500000,5000000);
	$judul = array(1 => '< Rp1jt','Rp1jt-Rp2.5jt','Rp2.5jt-Rp5jt','> Rp5jt');
	$jum = count($judul);
} elseif ($dasar == 'Agama' || $dasar == 'Suku') {		
	$query = "SELECT $tabel FROM jbsumum.$tabel";
	$result = QueryDb($query);
	$jum = @mysql_num_rows($result);	
} else {	
	$jum = 1;
}

for ($i=1;$i<=$jum;$i++) {	
	$field = "";
	if ($dasar == 'Golongan Darah' || $dasar == 'Jenis Kelamin' || $dasar == 'Kewarganegaraan' ) {
		$filter = "1 AND s.$tabel = '".$row[$i-1]."'";
	} elseif ($dasar == 'Penghasilan Orang Tua' ) {			
		$field = ", penghasilanayah+penghasilanibu";
		$filter = "1 AND ".$batas[$i-1]." < penghasilanayah+penghasilanibu < ".$batas[$i]." GROUP BY penghasilanayah+penghasilanibu";
		if ($i == $jum) {
			$filter = "1 AND ".$batas[$i-1]." > penghasilanayah+penghasilanibu GROUP BY penghasilanayah+penghasilanibu";
		} 
	} elseif ($dasar == 'Status Aktif') {
		$filter = $row[$i-1];		
	} elseif ($dasar=='Agama' || $dasar=='Suku' || $dasar=='Status Siswa' || $dasar=='Kondisi Siswa' || $dasar=='Pekerjaan Ayah' || $dasar=='Pekerjaan Ibu' || $dasar=='Pendidikan Ayah' || $dasar=='Pendidikan Ibu') {
		$row = @mysql_fetch_row($result);
		$judul[$i] = $row[0];		
		$filter = "1 AND s.$tabel = '$row[0]'";	
	} elseif ($dasar == 'Tahun Kelahiran') {
		$field = ", YEAR(tgllahir)";
		$filter = "1 GROUP BY YEAR(tgllahir)";	
		$j = 1;
		$jum = 0;		
	} elseif ($dasar == 'Usia') {
		$field = ", YEAR(now()) - YEAR(tgllahir)";
		$filter = "1 GROUP BY YEAR(now()) - YEAR(tgllahir)";	
		$j = 1;
		$jum = 0;		
	} else {
		$field =", s.$tabel";
		$filter = "1 GROUP BY s.$tabel";
		$j = 1;
		$jum = 0;
	}
		
	if ($departemen=="-1" && $idproses<0) {	
		$query1 = "SELECT COUNT(*) As Jum$field FROM jbsakad.calonsiswa s, jbsakad.prosespenerimaansiswa a WHERE a.replid=s.idproses AND s.aktif = $filter"; 
	}  
	if ($departemen<>"-1" && $idproses<0) {	
		$query1 = "SELECT COUNT(*) As Jum$field FROM jbsakad.calonsiswa s, jbsakad.prosespenerimaansiswa a WHERE a.departemen='$departemen' AND a.replid=s.idproses AND s.aktif = $filter";
	} 
	if ($departemen<>"-1" && $idproses>0) {	
		$query1 = "SELECT COUNT(*) As Jum$field FROM jbsakad.calonsiswa s, jbsakad.prosespenerimaansiswa a WHERE s.idproses=$idproses AND a.replid=s.idproses AND a.departemen='$departemen' AND s.aktif = $filter ";
	}
	
	$data[$i] = 0;	
	$result1 = QueryDb($query1);
	$num = @mysql_num_rows($result1);
	
	while ($row1 = @mysql_fetch_row($result1)) {
   		$data[$i] = $row1[0];
		if ($dasar=="Asal Sekolah" || $dasar=="Kode Pos Siswa" || $dasar=="Tahun Kelahiran" || $dasar=="Usia") { 
			$data[$j] = $row1[0];
			$judul[$j] = $row1[1];
			$j++;
			$jum = $jum+1;
		}
	} 
}

$sum = 0;
for ($i=1;$i<=$jum;$i++) {
	$sum = $sum + $data[$i];	
}

//$color = array(1 =>'green@0.5','yellow@0.5','red@0.5','blue@0.5','orange@0.5','gold@0.5','navy@0.5','darkblue@0.5','darkred@0.5','darkgreen@0.5', 'pink@0.5','black@0.5','gray@0.5');

//echo $queryL;
//Nih query tuk dapetin nama angkatannya===============

if ($departemen=="-1") {
	if ($idproses < 0){
		$query2 = "SELECT proses FROM jbsakad.prosespenerimaansiswa";
		} else {
		$query2 = "SELECT proses FROM jbsakad.prosespenerimaansiswa WHERE replid = $idproses";
		}
} else {		
	if ($idproses < 0){
		$query2 = "SELECT proses FROM jbsakad.prosespenerimaansiswa WHERE departemen='$departemen'";
		} else {
		$query2 = "SELECT proses FROM jbsakad.prosespenerimaansiswa WHERE replid = $idproses AND departemen='$departemen' ";
		}
}


$result2 = QueryDb($query2);
$row2 = @mysql_fetch_array($result2);
//=====================================================
/*if($sum == 0) {
  echo "<table width='100%' height='100%'><tr><td align='center' valign='middle'>
        <font size='2' face='verdana'>Grafik Lingkaran tidak dapat ditampilkan<br> karena belum ada data siswa<br> untuk Departemen <b>$_REQUEST[departemen]</b> dan Angkatan <b>$row[angkatan]</b></font></td></tr></table>";
}else {*/
//data
//$data = array_slice($data,0,$jum);
//$data = array($data[1],$data[2],$data[3],$data[4],$data[5]);
//$leg = array(" O ", " A ", " B ", " AB ", " Tidak ada data ");
//Buat grafik
$graph = new PieGraph(450,300,"auto");
$graph->img->SetAntiAliasing();
$graph->SetShadow();

$graph->title->Set("Statistik Calon Siswa Aktif Berdasarkan $dasar");

$graph->title->SetFont(FF_FONT1,FS_BOLD);

//$plot = new PiePlot3D($data);
$plot = new PiePlot($data);
$plot->ExplodeAll();
$plot->SetTheme("pastel");
$plot->SetShadow('gray@0.4');
$plot->SetLegends($judul);
$plot->SetSize(0.3);
$plot->SetCenter(0.4);

//memasukkan kedalam grafik
$graph->Add($plot);
//Menamplikan ke browser
$graph->Stroke();
//}
?>