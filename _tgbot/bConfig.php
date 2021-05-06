<?php

mb_internal_encoding("UTF-8");

set_time_limit(0);	//Время выполнения скрипта не ограничено
ob_implicit_flush();	//Включаем вывод без буферизации 
ignore_user_abort(true); // Игнорируем abort со стороны пользователя


// необходимые библиотеки

include_once ("../../lib/func_gen.php");
include_once ("../../lib/func_tg.php");

include_once ("../_db.php");

// все адреса бота

$BotToken="????";

$tgapi_url="https://api.telegram.org/bot";

$url=$tgapi_url.$BotToken."/";

$hook_url="https://????/";

$cronTab="cConfig";

$menuTab="mMenu";

$userTab="tbUser";

$vehicleTab="uVehicle";

$eventsTab="uEvents";

?>