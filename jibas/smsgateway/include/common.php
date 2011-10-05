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
require_once("sessionchecker.php");

$SMonth = array('Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des');
$LMonth = array('Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');

function RandStr($length) 
{
	$charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
	$s = "";
	while(strlen($s) < $length) 
		$s .= substr($charset, rand(0, 61), 1);
	return $s;		
}

function RandCode($length) 
{
	$charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
	$s = "";
	while(strlen($s) < $length) 
		$s .= substr($charset, rand(0, 61), 1);
	return $s;		
}

function RandNumber($length) 
{
	$charset = "1234567890";
	$s = "";
	while(strlen($s) < $length) 
		$s .= substr($charset, rand(0, 61), 1);
	return $s;		
}

function StringIsChecked($String,$Comparer)
{
	if ($String==$Comparer)
		echo "Checked";
}

function StringIsSelected($String,$Comparer)
{
	if ($String==$Comparer)
		echo "Selected";
}

function IntIsSelected($String,$Comparer)
{
	if ((int)$String==(int)$Comparer)
		echo "Selected";
}

function SDateFormat($string)
{
	global $LMonth;
	$x = split(' ',$string);
	$y = split('-',$x[0]);
	//echo $y[2].' '.$LMonth[(int)$y[1]-1].' '.$y[0];
	$m = ($y[1]-1);
	echo $y[2].'-'.$y[1].'-'.$y[0];
}

function DateFormat($string)
{
	global $LMonth;
	$x = split(' ',$string);
	$y = split('-',$x[0]);
	//echo $y[2].' '.$LMonth[(int)$y[1]-1].' '.$y[0];
	$m = ($y[1]-1);
	echo $y[2].' '.$LMonth[$m].' '.$y[0];
}

function DateFormat2($string)
{
	global $LMonth;
	$x = split(' ',$string);
	$y = split('-',$x[0]);
	$m = ($y[1]-1);

	if ($y[2]=='1')
		$ext = "st";
	elseif ($y[2]=='2')
		$ext = "nd";
	elseif ($y[2]=='3')
		$ext = "rd";
	else
		$ext = "th";
	$d = $y[2]; 
	if ($y[2]<10)
		$d = substr($y[2],1,1);
	echo $LMonth[$m].' '.$d.'<sup>'.$ext.'</sup>, '.$y[0];
}

function FullDateFormat($string)
{
	global $LMonth;
	
	$x = split(' ',$string);
	$y = split('-',$x[0]);
	$m = ($y[1]-1);
	
	echo $y[2].' '.$LMonth[$m].' '.$y[0].' '.$x[1];
}

function FullDateFormat2($string)
{
	global $LMonth;
	global $SMonth;
	
	$x = split(' ',$string);
	$y = split('-',$x[0]);

	$m = ($y[1]-1);
	if ($y[2]=='1')
		$ext = "st";
	elseif ($y[2]=='2')
		$ext = "nd";
	elseif ($y[2]=='3')
		$ext = "rd";
	else
		$ext = "th";
	$d = $y[2]; 
	if ($y[2]<10)
		$d = substr($y[2],1,1);
	echo $SMonth[$m].' '.$d.'<sup>'.$ext.'</sup>, '.$y[0].' on '.$x[1];
}

function MysqlDateFormat($string)
{
	$y = split('-',$string);
	return $y[2].'-'.$y[1].'-'.$y[0];
}

function GetLastId($field,$table)
{
	$sql = "SELECT MAX($field) FROM $table";
	$res = QueryDb($sql);
	$num = @mysql_num_rows($res);
	$row = @mysql_fetch_row($res);
	if ($num==0)
		return '1';
	else
		return $row[0]+1;
}

function ReplaceText($input,$output)
{
	return $output;
}
?>