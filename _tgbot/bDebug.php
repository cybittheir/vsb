<?php

###################################################################### - файл для отладки - #########################################################

// на период отладки записываем запросы в файлы

	if (isset($result_arr) AND is_array($result_arr)){
	
		while (list($r,$k)=each($result_arr)){
		
			$back_answ[]=$r." == ".$k;
		
		}

	}

	if (!isset($get_url)){$get_url="";}

	if (isset($back_answ) AND is_array($back_answ)){

		$resultRQ="\n************************\n[[".implode("\n",$back_answ)."]]\n\n".$get_url;

	}

	if (isset($resultRQ) AND isset($content)) {

		$file_mess=$content.$resultRQ;

	} elseif(is_array($update)) {

		$file_mess="json: ".$tg_query."\n\nurl: ".$get_url."\n-----\nresult: ".$result."\n\nPhoto: ".$plus_photo;

	} else {
	
		$file_mess="update: ".$update."\n\nurl: ".@$get_url."\n-----\nresult: ".@$result."\n\nPhoto: ".@$plus_photo;

	}

	$filename="qq/".date("His",time()).".txt";

	file_put_contents($filename,$file_mess);

######################################################################

?>