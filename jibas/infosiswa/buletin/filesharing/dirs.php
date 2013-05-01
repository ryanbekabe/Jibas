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
require_once("../../include/config.php");
require_once("../../include/common.php");
require_once("../../include/db_functions.php");
require_once("../../include/sessioninfo.php");
require_once("../../include/fileutil.php");
require_once('../../include/sessionchecker.php'); 

if (isset($_REQUEST[op]))
	$op = $_REQUEST[op];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="../../script/mktree.css" />
<script language="javascript" src="../../script/mktree.js"></script>
<script language="javascript" src="../../script/tools.js"></script>
<script language="javascript" src="../../script/tooltips.js"></script>
<link rel="stylesheet" href="../../style/tooltips.css" />
<link rel="stylesheet" href="../../style/style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>Untitled Document</title>
<script language="javascript">
function get_fresh()
{
	parent.files.location.href="blank.php";
	document.location.href="dirs.php?afteradd";
}

</script>
<style type="text/css">
<!--
.style1 {
	font-family: Calibri;
	font-weight: bold;
	font-size: 12px;
	color: #990000;
}
-->
</style>
</head>

<body style="background-color:#FBFBFB" <? if (isset($_REQUEST[afteradd])) { ?> onLoad="expandTree('tree1');" <? } ?>>
<a href="#" onClick="document.location.reload()"><img src="../../images/ico/refresh.png" border="0" /></a>&nbsp;|&nbsp;<a href="#" onClick="expandTree('tree1'); return false;">Expand All</a>&nbsp;|&nbsp;
<a href="#" onClick="collapseTree('tree1'); return false;">Collapse All</a><br /><br /><br />
<?
function getNSubDir($idroot)
{
	global $idvolume;
	
	$sql = "SELECT count(*) FROM jbsvcr.dirshare WHERE idroot='$idroot'";
	$result = QueryDb($sql);
	$row = mysql_fetch_row($result);
	
	return $row[0];
}

function spacing($count)
{
	$str = "";
	for ($i = 0; $i < $count * 2; $i++) 
		$str = $str . " ";
		
	return $str;
}

function traverse($iddir, $count)
{
	global $idvolume;
	
	$sql = "SELECT d.replid, d.dirname, d.idguru, p.nama FROM jbsvcr.dirshare d, jbssdm.pegawai p WHERE d.idroot='$iddir' AND p.nip=d.idguru ORDER BY d.dirname";
	$result = QueryDb($sql);
	$space = spacing($count);
	
	while ($row = mysql_fetch_row($result))
	{
		$ajar="";
		$msg="";
		$iddir = $row[0];
		$dirname = $row[1];
		$idguru = $row[2];
		$namaguru = $row[3];
		$nsubdir = getNSubDir($iddir);
		if ($dirname == $idguru)
		{
			$sql_get_wk = "SELECT p.nama as nama, k.kelas as kelas FROM jbsakad.kelas k, jbssdm.pegawai p WHERE p.nip='$idguru' AND k.nipwali=p.nip";
			$res_get_wk	= QueryDb($sql_get_wk);
			$num_wk = @mysql_num_rows($res_get_wk);
			$row_wk = @mysql_fetch_array($res_get_wk);
			$kelas = $row_wk[kelas];
			$namaguru = $row_wk[nama];

			$sql_get_id = "SELECT pel.nama as pelajaran, pel.departemen as dep FROM jbsakad.pelajaran pel, jbsakad.guru g WHERE g.nip='$idguru' AND g.idpelajaran=pel.replid";
			$res_get_id	= QueryDb($sql_get_id);
			$num_pel = @mysql_num_rows($res_get_id);
			if ($num_pel > 0)
			{
				$ajar="<b><u>Mengajar : </u></b>";
				$cnt=1;
				while ($row_pel = @mysql_fetch_array($res_get_id))
				{
					$ajar=$ajar."<br>".$cnt.". ".$row_pel[pelajaran]." (".$row_pel[dep].")";
					$cnt++;
				}
				$ajar=$ajar."<br>";
			}

			if ($num_wk > 0)
				$ajar=$ajar."<b><u> Walikelas ".$kelas." </u></b> <br>";

			if ($num_wk>0 || $num_pel>0)
				$msg="onMouseOver=\"showhint('$ajar', this, event, '140px')\"";
			else
				$msg="";	
		} // if
				
		if ($nsubdir == 0)
		{
			echo "$space<li class='liBullet'>&nbsp;</span><a $msg href='files.php?iddir=$iddir' style='text-decoration:none;' target='files'><img src='../../images/ico/folder.gif' border='0'>&nbsp;$dirname</a>&nbsp;";
			if (strtoupper(SI_USER_ID()) == "LANDLORD")
			{
				if ($msg == "")
					echo "<img onclick='delfolder($iddir)' src='../../images/ico/hapus.png'>";
			}
			echo "\r\n";
		}
		else
		{
			echo "$space<li class='liClosed'>&nbsp;<a $msg style='text-decoration:none;' href='files.php?iddir=$iddir' target='files'><img src='../../images/ico/folder.gif' border='0'>&nbsp;$dirname</a>&nbsp;";
			
			if (strtoupper(SI_USER_ID()) =="LANDLORD")
			{
				if ($msg == "")
					echo "<img onclick='delfolder($iddir)' src='../../images/ico/hapus.png'>";
			}
			echo "\r\n";
			echo "$space<ul>\r\n";

			$ajar="";
			
			traverse($iddir, ++$count);
			
			echo "$space</ul></li>\r\n";
		} //if 
	} //while
} //function

OpenDb();

$sql = "SELECT d.replid, d.dirname, d.idguru FROM jbsvcr.dirshare d WHERE d.idroot=0";
$result = QueryDb($sql);
if (mysql_num_rows($result) > 0)
{
	$row = mysql_fetch_row($result);
	$iddir = $row[0];
	$dirname = $row[1];
	$idguru = $row[2];
	$nsubdir = getNSubDir($iddir);
	
	echo "<ul class='mktree' id='tree1'>\r\n";
	if ($nsubdir == 0)
	{
		echo "&nbsp;<li class='liBullet'>&nbsp;<a style='text-decoration:none;' href='files.php?iddir=$iddir' target='files'><img src='../../images/ico/folder.gif' border='0'>&nbsp;(root)</a>&nbsp;";
		if (SI_USER_ID() == $idguru || strtoupper(SI_USER_ID()) =="LANDLORD")
			echo "</li>\r\n";
	}
	else
	{
		echo "&nbsp;<li class='liClosed'>&nbsp;<a style='text-decoration:none;' href='files.php?iddir=$iddir' target='files'><img src='../../images/ico/folder.gif' border='0'>&nbsp;(root)</a>&nbsp;";
		if (SI_USER_ID()==$idguru)
			echo "<img onclick='createfolder($iddir)' src='../../images/ico/tambah.png'>";
		echo "\r\n";
		echo "  <ul>\r\n";
		
		traverse($iddir, 2);
		
		echo "  </ul></li>\r\n";
	}
	echo "</ul>\r\n";
} //if 
CloseDb();
?>
<script language="javascript">
collapseTree('tree1');
</script>
<font face="Calibri" size="+1" color="#990000">
</body>
</html>