<?php


include_once("bConfig.php");

// https://api.telegram.org/bot[token]/setWebhook?url=[whook url]

//Инициализация запроса
$con = curl_init();

//Запоминаем в переменную дескриптор команды установки WebHook
$wh=$url."setWebhook?url=".$hook_url;

echo $wh;

//Устанавливаем опции запроса
curl_setopt($con, CURLOPT_URL, $wh);
curl_setopt($con, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($con, CURLOPT_HEADER, 0);

//Выполняем запрос, устанавливающий WebHook
$output = curl_exec($con);

//закрываем запрос
curl_close($con);

?>