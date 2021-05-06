<?php

function sql_query($query=''){

	if (!empty($query)){

        $b_host="localhost";
    	$b_user="?????";
    	$b_pass="??????";
    	$b_base="?????";

    	$odb = mysqli_connect($b_host,$b_user,$b_pass,$b_base);

    	if (mysqli_connect_errno()) {
	        printf("Connect failed: %s\n", mysqli_connect_error());
	        exit();
	    }
	
	    mysqli_select_db($odb,$b_base);
    	// в какой кодировке получать данные от клиента
    	@mysqli_query($odb,'set character_set_client="utf8"');
    	// в какой кодировке получать данные от БД для вывода клиенту
    	@mysqli_query($odb,'set character_set_results="utf8"');
    	// кодировка в которой будут посылаться служебные команды для сервера
        @mysqli_query($odb,'set collation_connection="utf8_general_ci"');
    
		$get_result=mysqli_query($odb,$query);

		if($get_result){$result=$get_result;}
		else {$result=false;}

		return $result;
	} 

}


?>