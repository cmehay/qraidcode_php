<?php
//fonctions génériques

function getencodedata($data, $type) {
  if($type === 'file'){
    trigger_error(str_replace(' ','+',substr($data,strpos($data,",")+1)));
    return base64_decode(str_replace(' ','+',substr($data,strpos($data,",")+1)));  
  }
  if($type === 'text'){
    return urldecode($data);
  }
  return false;  
}

function get_array($var, $a=null, $b=null, $c=null, $d=null){
  if(!isset($var)){return null;}
  if(!is_null($a) && !is_null($b) && !is_null($c) && !is_null($d)){
    if (!isset($var[$a])){return null;}
    if (!isset($var[$a][$b])){return null;}
    if (!isset($var[$a][$b][$c])){return null;}
    if (!isset($var[$a][$b][$c][$d])){return null;}
    return $var[$a][$b][$c][$d];
  }
  if(!is_null($a) && !is_null($b) && !is_null($c)){
    if (!isset($var[$a])){return null;}
    if (!isset($var[$a][$b])){return null;}
    if (!isset($var[$a][$b][$c])){return null;}
    return $var[$a][$b][$c];
  }
  if(!is_null($a) && !is_null($b)){
    if (!isset($var[$a])){return null;}
    if (!isset($var[$a][$b])){return null;}
    return $var[$a][$b];
  }
  if(!is_null($a)){
    if (!isset($var[$a])){return null;}
    return $var[$a];
  }
  return null;
}


?>