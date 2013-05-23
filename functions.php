<?php
//fonctions génériques

function getencodedata($data, $type) {
  if($type === 'file'){
    return base64_decode(str_replace(' ','+',substr($data,strpos($data,",")+1)));  
  }
  if($type === 'text'){
    return urldecode($data);
  }
  return false;  
}


?>