<?PHP

function checkAuth($userId){

    $User_Q="SELECT * FROM tbUsers WHERE tgUserId=:userId";

    $liveStatus="1";

    $sth=getDB()->prepare($User_Q);

    $sth->execute(array(":userId"=>$userId));

    if ($answer=$sth->fetch()){

        if ($answer['uStatus']=='1'){

            $result=$answer['uName'];

        } else {

            $result='?wop?';
        }

        return $result;

    } else {

        $UserAdd_Q="INSERT INTO tbUsers (tgUserId) VALUES (:tgUser)";

        $sth=getDB()->prepare($UserAdd_Q);

        $sth->execute(array(":tgUser"=>$userId));
    
        return false;

    }

}

function activeUser($userId,$userName){

    $UserActive_Q="UPDATE tbUsers SET uName=:uName, uStatus=:defStatus WHERE tgUserId=:tgUser";

    $defStatus=1;

    $sth=getDB()->prepare($UserActive_Q);

    if ($sth->execute(array(":tgUser"=>$userId,":uName"=>$userName,":defStatus"=>$defStatus))){

        return "Добро пожаловать, ".$userName."!\r\nТеперь вы можете использовать этот бот\r\nСначала нужно добавить ваше ТС: /addVh\r\nподсказки и основные команды: /help";

    } else {

        return "Ошибка активации. Обратитесь к разработчику";
    }


}

function clearPosition($userId){

    $UserActive_Q="UPDATE tbUsers SET botPosition=NULL, positionLive=NULL WHERE tgUserId=:tgUser";

        $sth=getDB()->prepare($UserActive_Q);

    if ($sth->execute(array(":tgUser"=>$userId))){

        return "OK";

    } else {

        return "Ошибка базы CLEAR_POS. Обратитесь к разработчику";
    }

}

function getUser($chatId,$field='position'){
// по-умолчанию возврат позиции, иначе - зависит от выбранного поля - id, vehicle, status, password

    $UserActive_Q="SELECT * FROM tbUsers WHERE tgUserId=:tgUser";

    $sth=getDB()->prepare($UserActive_Q);

    if ($sth->execute(array(":tgUser"=>$chatId))){

        if ($answer=$sth->fetch()){

            if ($field=='position' AND !empty($answer['botPosition'])){

                if (!empty($answer['positionLive']) AND $answer['positionLive']>date("Y-m-d H:i:s",time())){

                    $result=$answer['botPosition'];

                } else {
                    
                    clearPosition($chatId);
                    $result=false;
                }


            } elseif ($field=='vehicle' AND !empty($answer['selVehicle'])) {

                $result=$answer['selVehicle'];

            } elseif ($field=='status' AND !empty($answer['uStatus'])) {

                $result=$answer['uStatus'];

            } elseif ($field=='password' AND !empty($answer['uPass'])) {

                if ($answer['uPassTime']>date("Y-m-d H:i:s",time())){

                    $result=$answer['uPass'];
                
                } else {$result=false;}

            } elseif ($field=='id') {

                $result=$answer['uId'];

            } else {

                $result=false;

            }

        } else {

            $result=false;
        }

    } else {

        $result=false;
    }

    return $result;

}

function setPosition($chatId, $position=''){

    if (!empty($position)){

        $UserActive_Q="UPDATE tbUsers SET botPosition=:bPosition, positionLive=:pLive WHERE tgUserId=:tgUser";

        $sth=getDB()->prepare($UserActive_Q);

        $pLive=positionLive();

        if ($sth->execute(array(":tgUser"=>$chatId,":bPosition"=>$position,":pLive"=>$pLive))){

            return "OK";

        } else {

            return false;
        }

    } else {return false;}

}

function checkMenu($command){

    $menuQ="SELECT * FROM mMenu
        LEFT JOIN (SELECT mId as m2Id, mName as m2Name, mParent as m2Parent,mPosition as m2Position FROM mMenu) as m2Menu on m2Menu.m2Id=mParent
        WHERE mShort=:mShort";

    $sth=getDB()->prepare($menuQ);
    $sth->execute(array(":mShort"=>$command));
    
    if ($answer=$sth->fetch()){

        if ($result['name']=$answer['mName']){

            if (notEmpty($answer['m2Name'])){

                $result['name'].=". ".$answer['m2Name'];

            }

            $result['master']=$answer['mId'];

            if (notEmpty($answer['mLevel'])){
            
                $result['level']=$answer['mLevel'];
            
            }

            if (notEmpty($answer['mSingle'])){
            
                $result['single']=$answer['mSingle'];
            
            }

            if (notEmpty($answer['eTable'])){
            
                $result['table']=$answer['eTable'];
            
            }

            if (notEmpty($answer['m2Position'])){

                $position[]=$answer['m2Position'];

            }

            if (notEmpty($answer['mPosition'])){
            
                $position[]=$answer['mPosition'];
            
            }

            if (isset($position)) {

                $result['position']=implode("_",$position);

            }

            if (notEmpty($answer['defaultMonth'])){
            
                $result['defTime']=$answer['defaultMonth'];
            
            }

            if (notEmpty($answer['defaultDist'])){
            
                $result['defDist']=$answer['defaultDist'];
            
            }

        } else {
            
            $result['name']="something wrong";

        }
        
    } else {

        $result=false;
    }

    return $result;

}

function getMenuTitle($position,$field='id'){

    $pos_arr=explode("_",$position);

    if (notEmpty($pos_arr[2])){$lastPosition="_".$pos_arr[2];}
    elseif(notEmpty($pos_arr[1])){$lastPosition=$pos_arr[1];}
    else {$lastPosition=$pos_arr[0];}


    $menuQ="SELECT * FROM mMenu
        LEFT JOIN (SELECT mId as m2Id,mParent as m2Parent, mPosition as m2Position,mName as m2Name FROM mMenu) as m2Menu on m2Menu.m2Id=mParent
        WHERE mPosition=:mPosition";

    $sth=getDB()->prepare($menuQ);
    $sth->execute(array(":mPosition"=>$lastPosition));
    
    if ($answer=$sth->fetch()){

        if ($field=='id') {

            $result=$answer['mId'];

        } elseif($field=='name'){

            if (notEmpty($answer['m2Name'])){

                $result_arr[]=$answer['m2Name'];
    
            }
    
            $result_arr[]=$answer['mName'];
    
            $result=implode(".",$result_arr);

        } else {

            $result['text']=implode("",$result_arr);
            $result['id']=$answer['mId'];

        }
        
    } else {

        $result=$lastPosition;
    }

    return $result;

}

function getMenuTitleByID($menu_id){

    $menuQ="SELECT * FROM mMenu
        WHERE mId=:mId";

    $sth=getDB()->prepare($menuQ);
    $sth->execute(array(":mId"=>$menu_id));
    
    if ($answer=$sth->fetch()){

        $result=$answer['mName'];
        
    } else {

        $result=false;
    }

    return $result;

}

function positionLive($minutes='10'){
// возвращает время до которого позиция считается рабочей. 

    if (empty($minutes) OR intval($minutes)==0){
        $add_sec=600;
    } else {
        $add_sec=intval($minutes)*60;
    }

    return date("Y-m-d H:i:s",$add_sec+time());

}

function buildMenu($level='',$parent=''){

    if (empty($level)){

        $allQ_arr[]="mLevel IS NULL";

    } else {

        $_array=array(":mLevel"=>$level);

        $allQ_arr[]="mLevel=:mLevel";

    }

    if (!empty($parent)){

        $allQ_arr[]="(mParent IS NULL OR mParent=:mParent)";

        $_array[':mParent']=$parent;

    } else {$parentQ='';}

    $allQ=implode(" AND ",$allQ_arr);

    $menuQ="SELECT * FROM mMenu WHERE ".$allQ." ORDER BY mSort ASC";

    $sth=getDB()->prepare($menuQ);
    $sth->execute($_array);
    $answer=$sth->fetchAll();

    while (list($row,$res)=each($answer)){

        $menu_arr[]="/".$res['mShort']." - ".$res['mName'];

    }

    $menu=implode("\r\n",$menu_arr);

    return $menu;

}

function getVehicle($chatId) {

    $userId=getUser($chatId,'id');

    $selVehicle=getUser($chatId,'vehicle');

    $menu_arr[]="";

    $menuQ="SELECT * FROM uVehicle WHERE userId=:vUser AND vHide IS NULL ORDER BY vhName ASC";

    $_array=array(":vUser"=>$userId);

    $sth=getDB()->prepare($menuQ);
    $sth->execute($_array);
    $answer=$sth->fetchAll();

    while (list($r,$v)=each($answer)) {

        if ($selVehicle==$v['vId']){$selected="*";}
        else {$selected="\r\n| <i>сделать по-умолчанию:</i> /veh_def_".$v['vId'];}

        $change="\r\n| <i>изменить:</i> /veh_ed_".$v['vId'];

        $menu_arr[]="/veh_".$v['vId']." - '".$v['vhName']."'".$selected.$change;

    }

    if (sizeof($menu_arr)<2) {

        $menu_arr[]=" не указано ни одного ТС";
    
    }

    $menu_arr[]="";
    $menu_arr[]="/addVh - добавить ТС";

    $menu=implode("\r\n",$menu_arr);

    return $menu;

}

function getLastVehicle($chatId) {

    $userId=getUser($chatId,'id');

    $menuQ="SELECT * FROM uVehicle WHERE userId=:vUser AND vHide IS NULL ORDER BY created DESC LIMIT 1";

    $_array=array(":vUser"=>$userId);

    $sth=getDB()->prepare($menuQ);
    $sth->execute($_array);
    $answer=$sth->fetchAll();

    while (list($row,$res)=each($answer)){

        if (!isset($last_car)){$last_car=$res['vId'];}

    }

    if (notEmpty($last_car)){return $last_car;}
    else {return $false;}

}

function selectedVehicle($chatId) {

    if ($veh=getUser($chatId,'vehicle') AND intval($veh)>0){

        $vehQ="SELECT * FROM uVehicle WHERE vId=:vehicle LIMIT 1";

        $_array=array(":vehicle"=>$veh);

    } else {

        $userId=getUser($chatId,'id');

        $vehQ="SELECT * FROM uVehicle WHERE userId=:userId ORDER BY created DESC LIMIT 1";

        $_array=array(":userId"=>$userId);

        $fixNew=1;

    }

    $sth=getDB()->prepare($vehQ);
    $sth->execute($_array);
    $answer=$sth->fetchAll();

    if (sizeof($answer)==1) {

        while(list($k,$v)=each($answer)){

            $result_arr[]="<b>Меню для '".$v['vhName']."'";
    
            if (notEmpty($v['vBrend'])){$result_brend[]=$v['vBrend'];}
            if (notEmpty($v['vModel'])){$result_brend[]=$v['vModel'];}
            if (notEmpty($v['vYear'])){$result_brend[]=$v['vYear'];}

            if (isset($result_brend)){$result_arr[]=implode(".",$result_brend);}

            if (notEmpty($v['vNumber'])){$result_arr[]=$v['vNumber'];}

            $selV=$v['vId'];
            
        }

        if (notEmpty($fixNew)){

            $fixQ="UPDATE tbUsers SET selVehicle=:selV WHERE tgUserId=:tgUser";

            $_array=array(":selV"=>$selV,":tgUser"=>$chatId);
            $sth=getDB()->prepare($fixQ);
            $sth->execute($_array);

        }

        $result=implode("\r\n",$result_arr)."</b>\r\n";
            
        return $result;

    } else {

        return false;

    }

}

function notEmpty($var=''){

    if (!empty($var)) {return $var;}
    else {return false;}

}

function getQuestions($position){

    $command=explode("_",$position);

    $Quest="SELECT * FROM mQuestions WHERE position=:command";

    $_array=array(":command"=>$command[0]);

    $sth=getDB()->prepare($Quest);
    $sth->execute($_array);
    $answer=$sth->fetch();

    if (!isset($command[1])){
        $command[1]=0;
    }

    $num=$command[1]+1;

    $result['table']=$answer['useTable'];
    $result['keyId']=$answer['primaryId'];
    $result['question']=str_replace("<br>","\r\n",$answer['quest'.$num]);
    $result['field']=$answer['field'.$num];

    return $result;

}

function getLastHedge($chatId) {

    $userId=getUser($chatId,'id');

    $recQ="SELECT * FROM uHedge WHERE userId=:hUser ORDER BY recCreated DESC LIMIT 1";

    $_array=array(":hUser"=>$userId);

    $sth=getDB()->prepare($recQ);
    $sth->execute($_array);
    $answer=$sth->fetchAll();

    while (list($row,$res)=each($answer)){

        if (!isset($last_hedge)){$last_hedge=$res['hId'];}

    }

    if (notEmpty($last_hedge)){return $last_hedge;}
    else {return $false;}

}

function readHedge($vehicle,$kind='',$control='') {

    if ($control==1){
        $limit=2;
    } else {$limit=3;}

    if (empty($kind)){$kind="osago";}

    $recQ="SELECT * FROM uHedge WHERE vehicleId=:hVeh AND hType LIKE :hType ORDER BY recCreated DESC LIMIT ".$limit;

    $hType="%".strtoupper($kind)."%";

    $_array=array(":hVeh"=>$vehicle,":hType"=>$hType);

    $sth=getDB()->prepare($recQ);
    $sth->execute($_array);
    $answer=$sth->fetchAll();

    while (list($k,$val)=each($answer)){

        if (notEmpty($val['hNum'])){

            $_hedge[]=$val['hNum'];

        }

        if (notEmpty($val['hPlace'])){

            $_hedge[]=$val['hPlace'];

        }

        if (notEmpty($val['hEnd'])){

            $_hedge[]=$val['hEnd'];

            if (!isset($lastHedge)){

                $controlTime=time()+28*24*3600;

                $lastHedge=$val['hEnd'];
            }

        }

    }

    if (isset($_hedge)){

        if ($control!='1'){

            $hedges[]=implode("\r\n| ",$_hedge);

        }

        if ($lastHedge<date("Y-m-d",$controlTime)){$hedges[]="\r\n<b>|   Внимание!!!</b>\r\n|   <u>".$lastHedge."</u> у вас заканчивается страховка!!!\r\n";}

    } else {

        if ($control!='1'){

            $hedges[]="\r\nУдивительно, но не зафиксировано ни одной страховки.\r\n";

        }
    }

    return implode("\r\n",$hedges);

}

function getLastEvent($chatId){

    $userId=getUser($chatId,'id');

    $recQ="SELECT * FROM tRecord WHERE userId=:evUser ORDER BY RecTime DESC LIMIT 1";

    $_array=array(":evUser"=>$userId);

    $sth=getDB()->prepare($recQ);
    $sth->execute($_array);
    $answer=$sth->fetchAll();

    while (list($row,$res)=each($answer)){

        if (!isset($last_event)){
            $last_event['id']=$res['tId'];
            $last_event['code']=$res['RecCode'];
        }

    }

    if (notEmpty($last_event)){return $last_event;}
    else {return $false;}

}

function getTEvent($code){
// чтение даныых события из временного файли и перенос их таблицу событий vEvents. Возвращает menu_id

    if (notEmpty($code)){
        
        $recQ="SELECT * FROM tRecord WHERE RecCode=:rCode";

        $_array=array(":rCode"=>$code);

        $sth=getDB()->prepare($recQ);
        $sth->execute($_array);
        $answer=$sth->fetch();

        $result['menuid']=getMenuTitle($answer['position']);

        $result['user']=$answer['userID'];
        $result['vehicle']=$answer['vehicle'];

        $result['range']=$answer['evRange'];
        $result['amount']=$answer['evAmount'];
        $result['place']=$answer['evPlace'];

        if (!empty($answer['evDate'])){
            
            $result['date']=$answer['evDate'];

        } else {

            $result['date']=$answer['RecTime'];

        }

        if (!empty($answer['evPrice'])){$result['cost']=$answer['evPrice'];}

        if (!empty($answer['q1'])){$memo[]=$answer['q1'];}
        if (!empty($answer['q2'])){$memo[]=$answer['q2'];}

        if (isset($memo)){$result['memo']=implode(". ",$memo);}

        if (is_array($result) AND $record=writeEvent($result)) {

            delTEvent($code);

            return $result['menuid'];

        } else {return false;}
        
    } else {return false;}

}

function writeEvent($array){
// запись события в таблицу vEvents
    $_array=array(":eUser"=>$array['user'],":eMenu"=>$array['menuid'],":eVeh"=>$array['vehicle'],":eDate"=>$array['date'],":eRange"=>$array['range']);

    if (isset($array['cost'])){
        
        $field[]=",eCost";
        $value[]=",:eCost";
        $_array[":eCost"]=$array['cost'];

    }

    if (isset($array['amount'])){
        
        $field[]=",eAmount";
        $value[]=",:eAmount";
        $_array[":eAmount"]=$array['amount'];

    }

    if (isset($array['place'])){

        $field[]=",ePlace";
        $value[]=",:ePlace";
        $_array[":ePlace"]=$array['place'];

    }

    if (isset($array['memo'])){

        $field[]=",eMemo";
        $value[]=",:eMemo";
        $_array[":eMemo"]=$array['memo'];

    }

    $newQ="INSERT INTO vEvents (eUser,eVeh,eDate, eRange, menu_id".implode("",$field).") VALUES (:eUser,:eVeh,:eDate,:eRange,:eMenu".implode("",$value).")";

    $sth=getDB()->prepare($newQ);

    if ($sth->execute($_array)){return true;}
    else {
        return false;
    }

}

function delTEvent($key,$user=''){
// Очистка временного файла. по-умолчанию используется код, иначе - ID пользователя

    if (notEmpty($key)){

        if (empty($user)){

            $delQ="DELETE FROM tRecord WHERE RecCode=:rCode";

            $_array=array(":rCode"=>$key);

        } else {

            $delQ="DELETE FROM tRecord WHERE userID=:userID";

            $_array=array(":userID"=>$key);

        }

        $sth=getDB()->prepare($delQ);
        
        if($sth->execute($_array)){

            return true;

        } else {return false;}

    } else {return false;}

}

function readEvent($vehicle,$menu='',$period='12'){

    $_array=array(":eVeh"=>$vehicle);

    $month=intval($period);

    if ($month<1){$month=13;}
    elseif ($month>120){$month=120;}

    if ($menu=='3') {$month=2;}
    
    $lastTime=time()-$month*30*24*3600;

    $lastDate=date("Y-m-d H:i:s",$lastTime);

    $_array[":eDate"]=$lastDate;

    if (!empty($menu)){

        $_array[":eMenu"]=$menu;

        $recQ="SELECT * FROM vEvents WHERE eVeh=:eVeh AND menu_id=:eMenu AND eDate>:eDate ORDER BY eRange DESC";

    } else {

        $recQ="SELECT * FROM vEvents 
            LEFT JOIN mMenu on mId=menu_Id
            WHERE eVeh=:eVeh AND eDate>:eDate AND menu_id<>3
            ORDER BY eRange DESC";

    }

    $sth=getDB()->prepare($recQ);
    $sth->execute($_array);
    $answer=$sth->fetchAll();

    while (list($k,$v)=each($answer)){

        if (isset($v['mName'])){$vName="<b>".$v['mName']."</b>";}
        else {$vName=":: Дата";}

        $vdateExp=explode(" ",$v['eDate']);

        $values[]=$vName.": ".$vdateExp[0];
        $values[]=":: Километраж: ".$v['eRange'];

        if (!empty($v['eAmount'])){$values[]=":: Объем: ".$v['eAmount'];}
        if (!empty($v['ePlace'])){$values[]=":: Место: ".$v['ePlace'];}
        if (!empty($v['ePrice'])){$values[]=":: Стоимость: ".$v['ePrice'];}
        if (!empty($v['eMemo'])){$values[]=":: доп.:".$v['eMemo'];}

        $firstDate=$v['eDate'];

        $rows[]=implode("\r\n",$values);

        unset($values);
        unset($vdateExp);

    }

    if (isset($rows)){

        $dateExp=explode(" ",$firstDate);

        $msg="Записи с ".$dateExp[0]." <i>(в обратном порядке)</i>\r\n\r\n".implode("\r\n\r\n",$rows);

    } else {

        $recQ=str_replace(":eVeh","'".$vehicle."'",$recQ);
        $recQ=str_replace(":eDate","'".$lastDate."'",$recQ);
        $recQ=str_replace(":eMenu","'".$menu."'",$recQ);

        $msg=$menu."_За период (".$month." мес.) не было произведено никаких работ или записей"; //.\r\n".$recQ;

    }

    return $msg;

}

function calcFuel($vehicle,$period=''){
// вычисление расхода топлива. период в месяцах от сегоднешней даты или исходя из двух последних заправок
    
    $_array=array(":eVeh"=>$vehicle);
    
    if (empty($period)){
    
        $recQ="SELECT * FROM vEvents WHERE eVeh=:eVeh ORDER BY eRange DESC LIMIT 2";
    
        $res_text="(последние две)";
    
    } elseif ($month=intval($period) AND ($month>0 AND $month<24)){
    
        $lastTime=time()-$month*30*24*3600;
        $lastDate=date("Y-m-d H:i:s",$lastTime);
        $recQ="SELECT * FROM vEvents WHERE eVeh=:eVeh AND eDate>:eDate ORDER BY eRange DESC";
        $_array[":eDate"]=$lastDate;
        $res_text="(".$month." мес.)";
    
    }
    
    $sth=getDB()->prepare($recQ);
    $sth->execute($_array);
    $answer=$sth->fetchAll();
    
    while (list($row,$res)=each($answer)){
    
        if (!isset($numberMax)) {$numberMax=$res['eRange'];}
        else{
            $numberMin=$res['eRange'];
            $amount[]=$res['eAmount'];
        }
    
    }
    
    if (isset($numberMin)){
    
        $range=$numberMax-$numberMin;
        $fullAmount=array_sum($amount);        
        $result=($fullAmount/$range)*100;
        $rResult=round($result,1);
    
        $msg="--\r\nпо данным километража и заправок ".$res_text." расход топлива составляет ".$rResult." л на 100 км\r\n--";
    
    } else {
    
        $msg="";

    }
  
    return $msg;
    
}

function reminder($vehicle,$hedge=''){

    // читаем меню на позиции с параметрами отслеживания по-умолчанию
    //проверяем события на наличие записей с отслеживаемыми параметрами
    //проверяем разницу между последней записью (километраж и дата) и последней зафиксированной с параметром.

    // в следуюзей версии добавить персональный конфиг и сравнение с ним

    // сравниваем даты последних страховок с сегодняшней датой. Сообщаем если разница дат более 11 месяцев.

    $menu=defReminder();

    $lastQ="SELECT * FROM vEvents WHERE eVeh=:eVeh ORDER BY eRange DESC LIMIT 1";

    $sth=getDB()->prepare($lastQ);
    $sth->execute(array(":eVeh"=>$vehicle));

    $last=$sth->fetch();

    $lastRange=$last['eRange'];

    while(list($k,$v)=each($menu)){

        $eventsQ="SELECT * FROM vEvents WHERE eVeh=:eVeh AND menu_id=:menuId ORDER BY eRange DESC LIMIT 1";

        $sth=getDB()->prepare($eventsQ);
        $sth->execute(array(":eVeh"=>$vehicle,":menuId"=>$k));
    
        $reLast=$sth->fetch();

        if (isset($v['range']) AND !empty($reLast['eRange']) AND ($reLast['eRange']+$v['range'])<($lastRange+500)){
            $needWork=$reLast['eRange']+$v['range'];
            $msg[]=$needWork." км - ".getMenuTitleByID($k)."(".$lastRange." км)";
            unset($needWork);
        }

        if (isset($v['time'])){

            $controlTime=time()-(1+$v['time'])*30*24*3600;

            if ($reLast['eDate']>date("Y-m-d",$controlTime)) {

                $msg[]=getMenuTitleByID($k)." - истекает срок использования (".$v['time']." мес.). Последние работы были произведены ".$reLast['eDate'];

            }

        }

    }

    if (!empty($hedge) AND $hedge=readHedge($vehicle,'',1) AND !empty($hedge)){$msg[]=$hedge;}

    if (!isset($msg) OR sizeof($msg)<1){$msg[]="В ближайшее время нет плановых работ";}

    return $msg;
}

// 	"location": {"latitude":43.127285,"longitude":131.921120}

function defReminder(){

    $menuQ="SELECT mId,defaultMonth,defaultDist FROM mMenu WHERE defaultMonth IS NOT NULL OR defaultDist IS NOT NULL";

    $sth=getDB()->prepare($menuQ);
    $sth->execute(array());
    
    if ($answer=$sth->fetchAll()){

        while(list($k,$val)=each($answer)){

            if (!empty($val['defaultMonth'])){

                $result[$val['mId']]['time']=$val['defaultMonth'];
            }

            if (!empty($val['defaultDist'])){

                $result[$val['mId']]['range']=$val['defaultDist'];
            }
            
        }

    } else {

        $result=false;
    }

    return $result;

}

?>