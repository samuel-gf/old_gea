<?php	
	include_once('../lib.php');
	connectDB();
	//$_GET['m'] = ":reset";
	//$_GET['idBoard'] = 3;
	$m = preg_replace('/[ ]+/', ' ', secure_param('m'));
	$idBoard = intval(secure_param('idBoard'));

	if ($m == '' && $m != NULL && $idBoard <= 0) die("ERROR: Wrong parameters");
	$arrCommands = explode(';', $m);
	foreach($arrCommands as $kCommand => $command){
		$command = trim($command);
	
		if (preg_match("/@([^ ]*)/", $command, $arrTmp)){
			$name = array_key_exists(1, $arrTmp)?$arrTmp[1]:'';
			preg_match("/(\d+),(\d+)(,(\d+))?(,(\d+))?/", $command, $arrTmp);
			$x = array_key_exists(1, $arrTmp)?$arrTmp[1]:'';
			$y = array_key_exists(2, $arrTmp)?$arrTmp[2]:'';
			$w = array_key_exists(4, $arrTmp)?$arrTmp[4]:'1';
			$h = array_key_exists(6, $arrTmp)?$arrTmp[6]:'1';
		}

		# imagen.png
		preg_match("/!([^ ]*)/", $command, $arrTmp);
		$img_src = array_key_exists(1, $arrTmp)?$arrTmp[1]:'';

		# borde css
		preg_match("/_([^ ]*)/", $command, $arrTmp);
		$border = array_key_exists(1, $arrTmp)?$arrTmp[1]:'';

		# Dice command
		#if(preg_match("/#(\d*)d(\d*)(([\+\-])(\d*))?/", $command, $arrTmp)){
		if(preg_match("/#([^ ]*)/", $command, $arrTmp)){
			$strResults = '';
			$manual_command = 'dice';
			$arrDices = explode(',', $arrTmp[1]);
			$sDescription = '';
			foreach($arrDices as $oneDice){
				preg_match("/(\d*)d(\d*)(([\+\-])(\d*))?/", $oneDice, $arrDice);
				$n = $arrDice[1];
				$size = $arrDice[2];
				$mod = $arrDice[4]=='+'?$arrDice[5]:-($arrDice[5]);
				$result = 0;
				for ($i=0; $i<$n;$i++){
					$result += rand(1, $size);
				}
				$result += $mod;
				$strResults .= ' '.$result;
				$sDescription.= $n."d$size".($mod!="0"?$arrDice[4].$mod:"")."=<span class='red'>$result</span> ";
			}
			set_dice($idBoard, $name, $strResults);
			insert_action($idBoard, "@$name $sDescription");
		}
		
		# Other command		
		if(preg_match("/:reset/", $command, $arrTmp)){
			$manual_command = 'reset';
		}
	
		if ($manual_command != ''){
			switch ($manual_command){
			case 'reset':
				echo "RESET";
				reset_board($idBoard);
				break;
			}
		} else {
			insert_token($idBoard, $name, $x, $y, 1, $w, $h, $img_src, $border);
			insert_action($idBoard, $command);
		}
	}
