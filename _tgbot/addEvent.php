<?PHP

$questions=getQuestions($position);

$field=$questions['field'];
$table=$questions['table'];
$keyID=$questions['keyId'];

if (notEmpty($pos_arr[1])){

    $newQ="UPDATE ".$table." SET ".$field."=:answer WHERE ".$keyID."=:key";
    $msg=$newQ;

    $_array=array(":key"=>$pos_arr[2],":answer"=>$query_txt);

    $sth=getDB()->prepare($newQ);

    if ($sth->execute($_array)) {
   
        $pos_num=$pos_arr[1]+1;

        $new_position=$pos_arr[0]."_".$pos_num."_".$pos_arr[2];
    
        $_vh=getLastEvent($chatID);

        if($_vh){

            $questions=getQuestions($new_position);

            $msg=str_replace("%code%","/WR_".$_vh['code'],$questions['question']);

        } else {

            $questions=getQuestions($new_position);

            $msg=$questions['question'];

        }

        if(empty($questions['field'])){$new_position="";}

    } else {$msg=$newQ."\r\nОшибка добавления записи";}

} else {

    $rCode=generateSID(6);

    $vUser=getUser($chatID,'id');

    $uVeh=getUser($chatID,'vehicle');

    $newQ="INSERT INTO ".$table." (userId, vehicle,RecCode, position, ".$field.") VALUES (:vUser, :uVehicle,:rCode, :rPosition, :rQuery)";

    $_array=array(":vUser"=>$vUser,":uVehicle"=>$uVeh,":rCode"=>$rCode,":rPosition"=>$position,":rQuery"=>$query_txt);

    $sth=getDB()->prepare($newQ);

    if ($sth->execute($_array)) {

        $_vh=getLastEvent($chatID);

        if($_vh){

            $new_position=$pos_arr[0]."_1_".$_vh['id'];

            $questions=getQuestions($new_position);

            $msg=str_replace("%code%","/WR_".$_vh['code'],$questions['question']);

        } else {

            $msg="\r\nОшибка добавления события";

        }

    } else {

        $msg="\r\nОшибка добавления события для ".$rCode;

    }

}


?>