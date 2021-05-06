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

    $hDate=$query_txt;

    if (notEmpty($pos_arr[2])){
        
        $hType=$pos_arr[2];
    
        $veh=getUser($chatID,'vehicle');

        $newQ="INSERT INTO uHedge (userId,vehicleId,hType,hEnd) VALUES (:vUser,:vehicle,:hType,:hEnd)";

        $vUser=getUser($chatID,'id');

        $_array=array(":vUser"=>$vUser,":vehicle"=>$veh,":hType"=>$hType,":hEnd"=>$hDate);

        $sth=getDB()->prepare($newQ);

        if ($sth->execute($_array)) {

            $_vh=getLastHedge($chatID);

            if($_vh){

                $new_position=$pos_arr[0]."_1_".$_vh;

                $questions=getQuestions($new_position);

                $msg=str_replace("%code%",$vhName,$questions['question']);

            } else {

                $msg="\r\nОшибка добавления страховки";

            }

        } else {

            $msg=$newQ."\r\nОшибка добавления страховки '".$hType."' от '".$hDate."' для ".$vUser." (".$veh.")";

        }
    
    } else {$msg="\r\nОшибка добавления типа страховки";}

}

?>