<?PHP

if (notEmpty($pos_arr[1])){

    $questions=getQuestions($position);


    $field=$questions['field'];
    $table=$questions['table'];
    $keyID=$questions['keyId'];

    $newQ="UPDATE ".$table." SET ".$field."=:answer WHERE ".$keyID."=:key";

    $_array=array(":key"=>$pos_arr[2],":answer"=>$query_txt);

    $sth=getDB()->prepare($newQ);

    if ($sth->execute($_array)) {
   
        $pos_num=$pos_arr[1]+1;

        $new_position=$pos_arr[0]."_".$pos_num."_".$pos_arr[2];
    
        $questions=getQuestions($new_position);

        $msg=$questions['question'];

        if(empty($questions['field'])){$new_position="";}

    } else {$msg="\r\nОшибка добавления записи '".$query_txt."'";}

} else {

    $vhName=$query_txt;

    $newQ="INSERT INTO uVehicle (userId,vhName) VALUES (:vUser,:vhName)";

    $vUser=getUser($chatID,'id');

    $_array=array(":vUser"=>$vUser,":vhName"=>$vhName);

    $sth=getDB()->prepare($newQ);

    if ($sth->execute($_array)) {

        $_vh=getLastVehicle($chatID);

        if($_vh){

            $new_position=$position."_1_".$_vh;

            $questions=getQuestions($new_position);

            $msg=str_replace("%vhName%",$vhName,$questions['question']);

        } else {

            $msg="\r\nОшибка добавления ТС";

        }

    } else {

        $msg="\r\nОшибка добавления ТС '".$vhName."' для ".$vUser;

    }

}

?>