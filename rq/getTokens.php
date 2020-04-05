<?php
include_once('../lib.php');
connectDB();
$idBoard = secure_param('idBoard');
# $idBoard = 4;

# Get tokens from board
$query = "SELECT * FROM tokens WHERE idBoard = $idBoard;";
$result = run_sql($query) or die();
while($row = mysqli_fetch_array($result)){
	$dice_result = str_replace(' ',';',trim($row['dice_result']));
	if ($dice_result == '') $dice_result = 'null';
	echo $row['x'].' '.$row['y'].' '.$row['z'].' '.$row['w'].' '.$row['h'].' ';
	echo $row['step'].' '.$row['img'].' ';
	echo $row['name'].' '.$row['border'].' ';
	echo $dice_result.' '.$row['dice_actionId'];
	# Attrs
	$query = "SELECT * FROM attrs WHERE idBoard=$idBoard AND tokenName='".$row['name']."';";
	$result_attrs = run_sql($query) or die();
	$sAttrs = '';
	while($row_attr = mysqli_fetch_array($result_attrs)){
		$sAttrs.= $row_attr['attr'].':'.$row_attr['val'].',';
	}
	$sAttrs = trim($sAttrs, ',');
	echo ' '.$sAttrs;
	echo "\n";
}

