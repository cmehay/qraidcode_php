<?php
//fonctions génériques

function rrmdir($dir) {
    foreach(glob($dir . '/*') as $file) {
        if(is_dir($file))
            rrmdir($file);
        else
            unlink($file);
    }
    rmdir($dir);
}

function getencodedata($data, $type) {
  if($type === 'file'){
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

function set_hash_init(){
  if(is_file(BLACKLIST)){
    return json_decode(file_get_contents(BLACKLIST), true);
  }
  return array();
}

function delete_old(){
  if(isset($_SESSION['delete_old'])){return null;}
  foreach(array_diff(scandir(WORKDIR), array('..', '.', '.gitignore')) as $value) {
    $stat = stat(WORKDIR.$value);
    if(($_SERVER['REQUEST_TIME'] - $stat['mtime']) > TIMEOUT){
      rrmdir(WORKDIR.$value);
    }
  }
  $_SESSION['delete_old'] = true;
}
