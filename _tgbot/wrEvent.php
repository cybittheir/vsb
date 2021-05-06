<?PHP

$codeArr=explode("/WR_",$query_txt);

if ($menuId=getTEvent($codeArr[1])){

    $msg[]="Событие записано";

    clearPosition($chatID);
    
    if ($menuId=='3'){

        // нужно заменить ID на сравнение с mShort

        $uVeh=getUser($chatID,'vehicle');

        //вычисляем расход топлива в течении 1 месяца. Период нужно внести в конфиг пользователя
        
        if ($msg[]=calcFuel($uVeh,1)){;}

    }

    // проверка на необходимость проведения работ или продления страховки

    if ($msg[]=implode("\r\n",reminder($uVeh,1))){;}

    $msg[]="===================";
    $msg[]=$selVehicleMsg; //как заголовок меню

    $result_arr['text']=implode("\r\n",$msg).buildMenu();
    
}

?>