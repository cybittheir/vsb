<?PHP

$temp_arr[]="<i>".$_check['name']."</i>";


if ($query_txt=="/vehicle") {
    
    $temp_arr[]=getVehicle($chatID,'id');

} elseif (($query_txt=="/osago" OR $query_txt=="/casco") AND isset($_check['position'])) {
    
    $position=$_check['position'];

    setPosition($chatID,$position);

    $questions=getQuestions($position);

    $temp_arr[]=$questions['question'];

} elseif ($query_txt=="/charge" AND isset($_check['position'])) {

    $position=$_check['position'];

    setPosition($chatID,$position);

    $questions=getQuestions($position);

    $temp_arr[]=$questions['question'];

} elseif(isset($_check['position'])) {

    $position=$_check['position'];

    setPosition($chatID,$position);

    $questions=getQuestions($position);

    $temp_arr[]=$questions['question'];

} else {

    $temp_arr[]="Команда без обработки: ".$query_txt;

}

$result_arr['text']=implode("\r\n",$temp_arr);

?>