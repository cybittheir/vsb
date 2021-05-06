<?PHP

if (intval($chatID)!=0){
	
	if (isset($update['message']['photo']) AND $photo_arr=$update['message']['photo']) {

		include_once ($bot_lib_path."tgClass.php");   

		$comm_path="../../inc.common/";

		$tg = new tg($BotToken);

		$ardata = array("file_id" => $photo_arr[sizeof($photo_arr)-1]['file_id']);

		$zz = $tg->getPhoto($ardata);

		$fileBaseName="tg".strtotime(date("y-m-d H:i:s"));

		$filename = "img/".$fileBaseName.".jpg";

		$tg->savePhoto($zz["result"]["file_path"],$filename);

		if (file_exists($filename)) {

			$outfile = "img/".$fileBaseName.".jpg";

			$plus_photo=$outfile;
			
			$PHOTO_field=", PHOTO, idPHOTO";
			$PHOTO_val=", '".$plus_photo."', '".$ardata['file_id']."'";

		} else {

			$PHOTO_field="";
			$PHOTO_val="";
			$plus_photo="2".$filename;

		}

	} else {

		$PHOTO_field="";
		$PHOTO_val="";
		$plus_photo="0";

	}

	$tg_query_Rec=str_replace("\\","\\\\",$tg_query);

    $content=$tg_query."\n\n";

    $result_arr['text']=$query_txt;

	if (mb_substr($query_txt,0,1)=="/"){

		$fullCommand=mb_substr($query_txt,1);

		$commands=explode("_",$fullCommand);

        $_check=checkMenu($commands[0]);

		$command=$commands[0];

	}

	$userId=getUser($chatID,'id');

	$inPosition=getUser($chatID);

	if ($selVehicle=selectedVehicle($chatID)){$selVehicleMsg=$selVehicle."\r\n";}
	else {$selVehicleMsg="для работы с ботом нужно выбрать или добавить ТС\r\n\r\n";}

	if ($query_txt=="/home"){

		clearPosition($chatID);

		delTEvent($userId,1);

        $result_arr['text']=$selVehicleMsg.buildMenu();

	} elseif ($query_txt=="/help") {

		include_once($bot_lib_path."bHelp.php");

		$result_arr['text']=implode("\r\n",$help_txt);
		
		unset($help_txt);

	} elseif ($query_txt=="/info") {

		include_once($bot_lib_path."bInfo.php");

		$result_arr['text']=implode("\r\n",$info_txt);
		
		unset($info_txt);

	} elseif ($query_txt=="/remind") {

		$uVeh=getUser($chatID,'vehicle');

		$msg[]="<u>Актуальные напоминания:</u>\r\n";
		$msg[]=implode("\r\n",reminder($uVeh,1));
		
		$result_arr['text']=implode("\r\n",$msg);
		
	} elseif ($query_txt=="/stop") {

		clearPosition($chatID);

		delTEvent($userId,1);

        $result_arr['text']="<u>Удаление пользователя и всех его данных из базы.</u>\r\nВ очереди на разработку";

	} elseif ($query_txt=="/addVh") {

		setPosition($chatID,$command);

		$questions=getQuestions($command);

        $result_arr['text']=$questions['question'];

	} elseif (notEmpty($command) AND $command=="WR") {
// запись события
		$result_arr['text']="Write selected";
		
		include_once($bot_lib_path."wrEvent.php");

	} elseif (notEmpty($command) AND (isset($_check) AND is_array($_check))){

		$followTo=$_check['master'];

		$result_arr['text']=sizeof($_check).":(".$_check['level'].",".$followTo.")";

		if (notEmpty($_check['single']) AND $inPosition!='read'){

			include_once ($bot_lib_path."bSelector.php");

		} elseif (sizeof($_check)>2 AND notEmpty($_check['level'])){

			$readMenu=checkMenu($command);

			if ($action=checkMenu($inPosition)){
				
				if (empty($readMenu['position'])){
					$action_txt="";
					$selMenu='';
				} else {
					$action_txt=": ".$action['name'];
					$selMenu=$readMenu['master'];
				}

				if(!empty($action['defTime'])){

					$selTime=$action['defTime']+1;

				} else {$selTime='';}

			} else {$action_txt="";}

			$veh=getUser($chatID,'vehicle');

			$level=$_check['level'] + 1;

			setPosition($chatID,$inPosition);

			if (!empty($readMenu['single'])){

				if ($readMenu['table']=='uHedge'){

					$showEvents=readHedge($veh)."\r\n";

				} else {

					$showEvents=readEvent($veh,$selMenu,$selTime)."\r\n";

				} 

			} else {$showEvents="";}

			$result_arr['text']="<u>".$_check['name'].$action_txt."</u>\r\n\r\n".$showEvents.buildMenu($level,$followTo);

		} else {

			if ($action=checkMenu($inPosition)){$action_txt=": ".$action['name'];}
			else {$action_txt="";}

			setPosition($chatID,$command);

			$result_arr['text']="<u>".$_check['name'].$action_txt."</u>\r\n\r\n".buildMenu('1',$followTo);

		}

	} else {

		if ($position){

			$pos_arr=explode("_",$position);

			if ($pos_arr[0]=='addVh'){

				include_once($bot_lib_path."addVeh.php");

			} elseif ($pos_arr[0]=='evHedge'){

				include_once($bot_lib_path."addHedge.php");

			} else {
				
				include_once($bot_lib_path."addEvent.php");

			}

			if (notEmpty($new_position)){

				setPosition($chatID, $new_position);

			} else {clearPosition($chatID);}

			$result_arr['text']=$msg;

		} else {

			$result_arr['text']="прежде чем записать данные, вам нужно зайти в нужный раздел. Информация <u>'".$query_txt."'</u> не сохранена\r\n\r\n".$selVehicleMsg.buildMenu();
		}

    }

}

?>