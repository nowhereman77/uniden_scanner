<?php

date_default_timezone_set('MST');

Class Uniden{
  var $port = 50536;

  function Uniden($ip){
    $this->ip = $ip;

    if(!($this->sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
    {
      $errorcode = socket_last_error();
      $errormsg = socket_strerror($errorcode);
     
      die("Couldn't create socket: [$errorcode] $errormsg \n");
    }
  }
  
  function GetStatus(){
    $raw = $this->SendCommand('GSI');
    $raw = '<' . substr($raw, strpos($raw, '?'));
    $result = new SimpleXMLElement($raw);
    return $result;
  }
  
  function GetFavoriteLists(){
    $raw = $this->SendCommand('GLT,FL');
    $raw = '<' . substr($raw, strpos($raw, '?'));
    $result = new SimpleXMLElement($raw);
    return $result;
  }

  function GetSystemsForList($id){
    $raw = $this->SendCommand('GLT,SYS,' . $id);
    $raw = '<' . substr($raw, strpos($raw, '?'));
    $result = new SimpleXMLElement($raw);
    return $result;
  }

  function GetDepartmentsForSystem($id){
    $raw = $this->SendCommand('GLT,DEPT,' . $id);
    $raw = '<' . substr($raw, strpos($raw, '?'));
    $result = new SimpleXMLElement($raw);
    return $result;
  }

  function GetSitesForSystem($id){
    $raw = $this->SendCommand('GLT,SITE,' . $id);
    $raw = '<' . substr($raw, strpos($raw, '?'));
    $result = new SimpleXMLElement($raw);
    return $result;
  }

  function GetServiceSettings(){
    $raw = $this->SendCommand('SVC');
    $result = explode(',', $raw);
    return $result;
  }

  function SetVolume($v){
    $this->SendCommand('VOL,' . $v);
  }

  function SetWeather($s){
    $info = $this->GetStatus();
    
    if(strtoupper($info->DualWatch['WX']) != strtoupper($s)){
      $this->ToggleWeather();
    }
  }

  function ToggleWeather(){
    $this->SendCommand('KEY,C,P');
    sleep(1);
    $this->SendCommand('KEY,F,P');
    sleep(1);
    $this->SendCommand('KEY,6,P');
    sleep(5);
    $this->SendCommand('KEY,C,P');
  }

  function SendCommand($cmd){
    $cmd .= "\r";
    if( ! socket_sendto($this->sock, $cmd , strlen($cmd) , 0 , $this->ip , $this->port))
    {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
         
        die("Could not send data: [$errorcode] $errormsg \n");
    }
         
    //Now receive reply from server and print it
    if(socket_recv ( $this->sock , $reply , 4096, MSG_WAITALL ) === FALSE)
    {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
         
        die("Could not receive data: [$errorcode] $errormsg \n");
    }

    return $reply;
  }
}

?>

