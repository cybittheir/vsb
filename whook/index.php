<?php

date_default_timezone_set('Asia/Vladivostok');

header('Content-Type: text/html; charset=utf-8'); // кодировка UTF-8
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1

$bot_lib_path="../_tgbot/";

// Закрыть после отладки

include_once ($bot_lib_path."bErrSet.php");

// bot_token, etc

include_once ($bot_lib_path."bConfig.php");

Require ($bot_lib_path."tbLib.php");

Require ($bot_lib_path."tbClass.php");

$tg=new telegramBot($BotToken);

//читаем результат из стандартного потока, в который PHP записывает полученные данные

$tg_query=file_get_contents('php://input');

// на период отладки записываем запросы в файлы
$filename="qq/".date("His",time()).".txt";

$request_code = generateSID('18');

// чтение json
$update = json_decode($tg_query,true);

//получаем значение chat_id – идентификатор чата с пользователем, отправившим сообщение

$chatID=$update['message']['chat']['id'];
$tgUserName=$update['message']['chat']['first_name'];
$query_txt=$update['message']['text'];

Require ($bot_lib_path."tbDatabase.php");

if ($userName=checkAuth($chatID) AND ($userName=="?wop?" AND $query_txt=="cybittheirs_bot")) {

	$result_arr['text']=activeUser($chatID,$tgUserName);

} elseif(isset($userName) AND $userName!=false AND $userName!="?wop?"){

################# - Обработка входящего сообщения/команды - ###############

	$position=getUser($chatID);

	include_once ($bot_lib_path."bMenu.php");

} else {

	include_once($bot_lib_path."bInfo.php");

 	$result_arr['text']="Ограниченное использование. Требуется авторизация. За кодом доступа можно обратиться к @cybittheir_trv.\r\n\r\n".implode("\r\n",$info_txt);

}

//Формируем строку запроса – отправка пользователю сообщения

if (isset($result_arr)){

	$msg_txt=str_replace("\r\n","%0A",$result_arr['text']);

} elseif (isset($result)) {

	$msg_txt="Ошибка: некорректный запрос или команда. Или функционал команды находится в процессе разработки. Список возможных команд - /help";

} else {

	$msg_txt=str_replace("\r\n","%0A",'Ошибка');
	
}

//$msg=$url."sendMessage?chat_id=".$chatID."&parse_mode=html&text=".$msg_txt;

if ($output=send2Bot($url,$chatID,$msg_txt)){

#	$delQ="DELETE FROM tg_query WHERE REQUEST='".$request_code."'";

#	sql_query($delQ); // удаляем все следы запроса
	$result_arr['tb_err']="успешно";
} else {$result_arr['tb_err']="не отправлено";}

################# - файл для отладки - ###############

include_once ($bot_lib_path."bDebug.php");

######################################################

?>