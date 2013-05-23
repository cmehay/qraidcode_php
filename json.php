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
  if(is_array($_POST['data'])){
    json_error();
  }
  //décode les données
  
  
  $length = strlen($_POST['data']);
  if($length > MAXINPUT || $length == 0){
    return json_error('badsize');  
  }
  $min = ceil($length / 2891);
  if($min > MAXQRCODES){
    return json_error('badsize');
  }
  $_SESSION['data'] = $data;
  return json_valid(array(
      'maxqr' => MAXQRCODES,
      'minqr' => $min,
      'maxrs' => MAXQRCODES - $min,
      'minrs' => 0,
      'size' => $length
  )); 
}




?>