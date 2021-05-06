<?php

date_default_timezone_set('Asia/Vladivostok');

header('Content-Type: text/html; charset=utf-8'); // кодировка UTF-8
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1

// Закрыть после отладки

# include_once ("bErrSet.php");

//

include_once ("bConfig.php");

//читаем результат из стандартного потока, в который PHP записывает полученные данные

$tg_query=file_get_contents('php://input');

// на период отладки записываем запросы в файлы
$filename="qq/".date("His",time()).".txt";

$request_code = generateSID('18');

// чтение json
$update = json_decode($tg_query,true);

//получаем значение chat_id – идентификатор чата с пользователем, отправившим сообщение

$chatID=$update['message']['chat']['id'];

if (intval($chatID)!=0){
	
	$query_txt=$update['message']['text'];

	if (isset($update['message']['photo']) AND $photo_arr=$update['message']['photo']) {

		include_once ("tgClass.php");   

		$comm_path="../inc.common/";

#		include_once($comm_path."lib/func_img.php");

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

	$story_requestQ="INSERT INTO tg_query(MESSAGE_ID, REQUEST, RTXT, bNAME".$PHOTO_field.") VALUES ('".$chatID."','".$request_code."','".$tg_query_Rec."','prizm'".$PHOTO_val.")";

	if (sql_query($story_requestQ)){$content=$tg_query."\n\nSTORY\n\n".$story_requestQ."\n\n";} ############################### для отладки. Изменить
	else {$content=$tg_query."\n\nSTORY???\n".$story_requestQ."\n\n";}

//отправляем на сервер id запроса с telegram и читаем результат

	$get_url=$base_url."?req=".urlencode($request_code);

	if ($result=file_get_contents($get_url)) {
		$result_arr=json_decode($result,true);
	}

}

################# - файл для отладки - ###############

 include_once ("bDebug.php");

######################################################

//Формируем строку запроса – отправка пользователю сообщения

if (isset($result_arr)){

	$msg_txt=str_replace("\r\n","%0A",$result_arr['text']);

} elseif ($result) {

	$msg_txt="Ошибка: некорректный запрос или команда. Или функционал команды находится в процессе разработки. Список возможных команд - /help";

} else {

	$msg_txt=str_replace("\r\n","%0A",'Ошибка');
	
}

//$msg=$url."sendMessage?chat_id=".$chatID."&parse_mode=html&text=".$msg_txt;

if ($output=send2Bot($url,$chatID,$msg_txt)){

#	$delQ="DELETE FROM tg_query WHERE REQUEST='".$request_code."'";

#	sql_query($delQ); // удаляем все следы запроса

}

?>