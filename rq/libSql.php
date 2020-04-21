<?php

$db = null;

function connectDB(){
	global $db;
	// Host, user, passwd, db_name
	if (!array_key_exists('REMOTE_ADDR', $_SERVER)){
		$db = mysqli_connect("localhost", "gea", "gea", "gea");
	} else 
		if ($_SERVER['REMOTE_ADDR']=='127.0.0.1'){
			$db = mysqli_connect("localhost", "gea", "gea", "gea");
		} else {
			$db = mysqli_connect("db5000148109.hosting-data.io:3306", "dbu120009", "S4!4m4nc4", "dbs143332");
		}
	
	if (!$db){
		echo "ERROR: Cannot connect to DB\n";
		die();
	} 
}

function reset_db(){
	global $db;
	$query = "TRUNCATE actions;";
	run_sql($query) or die();
	$query = "TRUNCATE attrs;";
	run_sql($query) or die();
	$query = "TRUNCATE boards;";
	run_sql($query) or die();
	$query = "TRUNCATE guidelines;";
	run_sql($query) or die();
	$query = "TRUNCATE tokens;";
	run_sql($query) or die();
}

function secure_param($name){
	return (array_key_exists($name, $_GET))?$_GET[$name]:NULL;
}

function run_sql($query){
	global $db;
	$result = mysqli_query($db, $query);
	if ($result == false){
		error_mysqli($query);
	}
	return $result;
}

function error_mysqli($query){
	global $db;
    echo "ERROR ".mysqli_errno($db).': '.mysqli_error($db).' with query: '.$query;
}

function getTime(){
	$sRet = '<time>'.date('G:i').'</time>';
	return $sRet;
}

function write_last_actionId($idBoard, $actionId){
	global $db;
	$query = "UPDATE boards SET lastActionId=$actionId WHERE id=$idBoard;";
	run_sql($query) or die();
}

function increase_last_actionId($idBoard, $ammount){
	global $db;
	$query = "UPDATE boards SET lastActionId = lastActionId + $ammount;";
	run_sql($query) or die();
}
function read_last_actionId($idBoard){
	global $db;
	$query = "SELECT lastActionId FROM boards WHERE id=$idBoard LIMIT 1;";
	$result = mysqli_query($db, $query) or die();
	$lastId=0;
	if ($result->num_rows > 0){
		$arr = mysqli_fetch_array($result, MYSQLI_ASSOC);
		$lastId = $arr['lastActionId'];
	}
	return $lastId;
}

# Inser action in the DB table
function insert_action($idBoard, $m){
	global $db;
	$query = "SELECT MAX(number) FROM actions WHERE idBoard=$idBoard AND idUser=1 LIMIT 1";
	$result = run_sql($query) or die();
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	$next = intval($row['MAX(number)'])+1;
	$query = "INSERT INTO `actions` (`idUser`, `idBoard`, `number`, `ts`, `action`) VALUES ('1', '$idBoard',";
	$query.= " $next, CURRENT_TIMESTAMP, '".utf8_decode(mysqli_real_escape_string($db, $m))."');";
	run_sql($query) or die();
}

function set_guideline($idBoard, $tokenName, $guideline){
	global $db;
	if (!property_exists($guideline, 'n')) $guideline->n = -1;
	if (!property_exists($guideline, 'maxn')) $guideline->maxn = -1;
	$query = "INSERT INTO `guidelines` (idBoard, tokenName, guideNumber, name, guideAction, n, maxn) ";
	$query.= "VALUES ($idBoard, '$tokenName', $guideline->number, '$guideline->name', '$guideline->action', ";
	$query.= " $guideline->n, $guideline->maxn) ";
	$query.= " ON DUPLICATE KEY UPDATE guideAction='$guideline->action'";
	run_sql($query) or die();
	$nextActionId = intval(read_last_actionId($idBoard))+1;
	$query = "UPDATE tokens SET actionId=$nextActionId WHERE idBoard=$idBoard";
    $query.= " AND name='$tokenName'";
	run_sql($query) or die();
	increase_last_actionId($idBoard, 1);
}

function guideline_remove_counter($idBoard, $tokenName, $guideNumber){
	global $db;
	$query = "UPDATE guidelines SET n=n-1 WHERE idBoard=$idBoard AND tokenName='$tokenName' AND guideNumber=$guideNumber";
	run_sql($query) or die();
	increase_last_actionId($idBoard, 1);
}

function guideline_get_n($idBoard, $tokenName, $guideNumber){
	global $db;
	$query = "SELECT n FROM guidelines WHERE idBoard=$idBoard AND tokenName='$tokenName' AND guideNumber=$guideNumber";
	$result = run_sql($query) or die();
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	return $row['n'];
}

# Insert token in database, if there is not $img_src or $border ignore it
# If the token id is duplicate, just update it
function insert_token($idBoard, $name, $x, $y, $z, $w, $h, $img_src, $border, $file){
	global $db;
	$name = ($name=='')?'NULL':$name;
	$nextActionId = intval(read_last_actionId($idBoard))+1;
	$query = "INSERT INTO `tokens` (`idBoard`,`name`,file,`x`,`y`,`z`,`w`,`h`,`step`,`img`,`border`, `actionId`, `dice_result`) ";
	$query.= " VALUES ('$idBoard', '$name', '$file', $x, $y, $z, $w, $h, 1, ";
	$query.= "'$img_src', '$border',$nextActionId, NULL) ";
	$query.= " ON DUPLICATE KEY UPDATE x=$x, y=$y";
	if ($img_src != ''){
		$query.= ", img='$img_src'";
	}
	if ($border != ''){
		$query.= ", border='$border'";
	}
	run_sql($query) or die();
	increase_last_actionId($idBoard, 1);
}

function move_token($idBoard, $name, $x, $y){
	global $db;
	$name = ($name=='')?'NULL':$name;
	$nextActionId = intval(read_last_actionId($idBoard))+1;
	$query = "UPDATE `tokens` SET x=$x, y=$y, actionId=$nextActionId WHERE idBoard=$idBoard AND name='$name'";
	run_sql($query) or die();
	increase_last_actionId($idBoard, 1);
}

function set_attr($idBoard, $name, $attr, $val){
	global $db;
	$query = "INSERT INTO attrs (idBoard, tokenName, attr, val) ";
	$query.= "VALUES ($idBoard,'$name','$attr',$val) ";
	$query.= " ON DUPLICATE KEY UPDATE val='$val'";
	run_sql($query) or die();
	$nextActionId = intval(read_last_actionId($idBoard))+1;
	$query = "UPDATE tokens SET actionId=$nextActionId WHERE idBoard=$idBoard AND name='$name'";
	run_sql($query) or die();
	increase_last_actionId($idBoard, 1);
}

function reset_board($idBoard){
	global $db;
	$query = "DELETE FROM actions WHERE idBoard = $idBoard;";
	run_sql($query) or die();
	$query = "UPDATE boards SET lastActionId = 0;";
	run_sql($query) or die();
	$query = "DELETE FROM tokens WHERE idBoard = $idBoard;";
	run_sql($query);
	$query = "DELETE FROM attrs WHERE idBoard = $idBoard;";
	run_sql($query);
	$query = "DELETE FROM guidelines WHERE idBoard = $idBoard;";
	run_sql($query);
}

# Updates the dice column of a token
function set_dice($idBoard, $name, $value, $targets=''){
	global $db;
	$query = "SELECT lastActionId FROM boards WHERE id = $idBoard LIMIT 1;";
	$result = run_sql($query) or die();
	$row = mysqli_fetch_array($result);
	$nextActionId = intval(read_last_actionId($idBoard))+1;
	$dice_action_targets = trim($targets,',');
	$query = "UPDATE tokens SET dice_result = '$value', dice_actionId=$nextActionId, ";
	$query.= "dice_action_targets = '$dice_action_targets', actionId=$nextActionId  ";
    $query.= " WHERE idBoard = $idBoard AND name = '$name';";
	run_sql($query) or die();
	increase_last_actionId($idBoard, 1);
}


function get_bg_filename($idBoard){
	global $db;
	$query = "SELECT bg FROM boards WHERE id = $idBoard LIMIT 1;";
	$result = run_sql($query) or die();
	$row = mysqli_fetch_array($result);
	$bg_name = $row['bg'];
	return $bg_name;
}

function get_bg_ts($idBoard){
	global $db;
	$bg_file_name = get_bg_filename($idBoard);
	$bg_ts = filemtime('../img/bg/'.$bg_file_name);
	return $bg_ts;
}

function remove_token($idBoard, $name){
	global $db;
	$nextActionId = intval(read_last_actionId($idBoard))+1;
	$query = "UPDATE boards SET lastActionId = $nextActionId";
	run_sql($query) or die();
	/*
	$query = "DELETE FROM tokens WHERE idBoard = $idBoard AND name='$name';";
	run_sql($query);
	$query = "DELETE FROM attrs WHERE idBoard = $idBoard AND tokenName='$name';";
	run_sql($query);
	$query = "DELETE FROM guidelines WHERE idBoard = $idBoard AND tokenName='$name';";
	run_sql($query);
	 */
}

function get_token($idBoard, $name){
	global $db;
	$query = "SELECT * FROM tokens WHERE idBoard=$idBoard AND name='$name' LIMIT 1";
	$result = run_sql($query) or die();
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	return $row;
}

function get_attrs($idBoard, $name){
	global $db;
	$query = "SELECT attr,val FROM attrs WHERE idBoard=$idBoard AND tokenName='$name'";
	$result = run_sql($query) or die();
	$arrAttrs = Array();
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
		$arrAttrs[$row['attr']] = $row['val'];
	}
	return $arrAttrs;
}

function get_guidelines($idBoard, $name){
	global $db;
	$query = "SELECT guideNumber, name, n, maxn FROM guidelines WHERE idBoard=$idBoard AND tokenName='$name'";
	$result = run_sql($query) or die();
	#$result = mysqli_query($db, $query);
	$arrGuidelines = Array();
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
		$arrGuidelines[$row['guideNumber']] = Array('name'=>$row['name'],
			'n'=>$row['n'], 'maxn'=>$row['maxn']);
	}
	return $arrGuidelines;
}

function get_guideline($idBoard, $tokenName, $guideNumber){
	global $db;
	if ($guideNumber == 0){
		$guideNumber = get_default_guideline_id($idBoard, $tokenName);
	}
	$query = "SELECT * FROM guidelines WHERE idBoard=$idBoard AND tokenName='$tokenName' AND guideNumber=$guideNumber LIMIT 1";
	$result = run_sql($query) or die();
	return mysqli_fetch_array($result, MYSQLI_ASSOC);
}

function insert_board($board){
	global $db;
	$query = 'INSERT INTO boards (id, name, tilew, tileh, ntilesw, ntilesh, offsetx, offsety, bg, drawGrid, lastActionId)';
	$query.= " VALUES (null, '$board->name', $board->tilew, $board->tileh, $board->ntilesw, $board->ntilesh, ";
	$query.= " $board->offsetx, $board->offsety, '$board->bg', $board->drawGrid, 0) ";
	$result = run_sql($query) or die();
	return mysqli_insert_id($db);
}

function set_default_guideline_id($idBoard, $tokenName, $guide_id){
	global $db;
	$query = "UPDATE tokens SET defaultGuideline=$guide_id WHERE idBoard=$idBoard AND name='$tokenName'";
	$result = run_sql($query) or die();
	$nextActionId = intval(read_last_actionId($idBoard))+1;
	$query = "UPDATE tokens SET actionId=$nextActionId WHERE idBoard=$idBoard";
    $query.= " AND name='$tokenName'";
	run_sql($query) or die();
	increase_last_actionId($idBoard, 1);
}

function get_default_guideline_id($idBoard, $tokenName){
	global $db;
	$query= "SELECT defaultGuideline FROM tokens WHERE idBoard=$idBoard AND name='$tokenName' LIMIT 1";
	$result = run_Sql($query) or die();
	return (mysqli_fetch_array($result))[0];
}
?>