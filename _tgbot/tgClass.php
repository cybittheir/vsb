<?php

class tg {
  
  public function __construct($token) {
      $this->token = $token; 
  }
    
  public function send($id, $message,$keyboard) {   
      
      //Удаление клавы
      if($keyboard == "DEL"){		
          $keyboard = array(
              'remove_keyboard' => true
          );
      }
      if($keyboard){
          //Отправка клавиатуры
          $encodedMarkup = json_encode($keyboard);
          
          $data = array(
              'chat_id'      => $id,
              'text'     => $message,
              'reply_markup' => $encodedMarkup
          );
      }else{
          //Отправка сообщения
          $data = array(
              'chat_id'      => $id,
              'text'     => $message
          );
      }
     
      $out = $this->request('sendMessage', $data);       
      return $out;
  }         
  
  public function getPhoto($data){
      $out = $this->request('getFile', $data);        
      return $out;
  }  
  
  public function savePhoto($url,$puth){
      $ch = curl_init('https://api.telegram.org/file/bot' . $this->token .  '/' . $url);
      $fp = fopen($puth, 'wb');
      curl_setopt($ch, CURLOPT_FILE, $fp);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_exec($ch);
      curl_close($ch);
      fclose($fp);
  }
  
  public  function request($method, $data = array()) {
      $curl = curl_init(); 
        
      curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot' . $this->token .  '/' . $method);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data); 
        
      $out = json_decode(curl_exec($curl), true); 
        
      curl_close($curl); 
        
      return $out; 
  }
}

?>