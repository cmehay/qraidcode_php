<?php
//json generic
function json_error($error='error'){
  return json_encode(array(
      'error' => true,
      'msg' => $error
  ));
}

function json_valid($array){
  $array['error'] = false;
  return json_encode($array);
}




//jsons callbacks

function json_cb_get_encode_data(){
  $data = $_POST['data'];
  if(is_array($data)){
    json_error();
  }
  //décode les données
  $raw = getencodedata($data, $_GET['type']);
  if($raw === false){
    json_error();
  }
  
  $length = strlen($raw);
  if($length > MAXINPUT || $length == 0){
    return json_error('badsize');  
  }
  $min = ceil($length / 2891);
  if($min > MAXQRCODES){
    return json_error('badsize');
  }
  $_SESSION['data'] = $raw;
  return json_valid(array(
      'maxqr' => MAXQRCODES,
      'minqr' => $min,
      'maxrs' => MAXQRCODES - $min,
      'minrs' => 0,
      'size' => $length
  )); 
}

function json_cb_get_encode_option(){
  sleep(10);
  return json_valid(array(
      null
  ));
}

function json_cb_get_encode_status(){
  return json_valid(array(
      'msg' => 'caca :3'
  ));
}




?>