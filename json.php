<?php
//json generic
function json_error($error='error'){
  return json_encode(array(
      'error' => true,
      'msg' => $error
  ));
}

function json_valid($array=array()){
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
  
  if($_GET['name'] != ''){
    if(is_array($_GET['name'])){
      return json_error();
    }
    $raw = urldecode($_GET['name']).'/'.$raw;
  }else{
    $raw = $_GET['type'].'/'.$raw;
  }
  $length = strlen($raw);
  if($length > MAXINPUT || $length == 0){
    return json_error('badsize');  
  }
  $min = ceil($length / MAXDATA);
  if($min > MAXQRCODES){
    return json_error('badsize');
  }
  trigger_error($raw);
  $_SESSION['data'] = $raw;
  $_SESSION['sha1'] = hash('sha1', $raw, false);
  $_SESSION['datalength'] = $length;
  return json_valid(array(
      'maxqr' => MAXQRCODES,
      'minqr' => $min,
      'maxrs' => MAXQRCODES - $min,
      'minrs' => 0,
      'size' => $length
  )); 
}

function json_cb_get_encode_option(){
  //check inputs
  if(!isset($_POST['chunks']) || !isset($_POST['rs']) || !isset($_POST['size'])){
    return json_error();
  }
  $nb = (int)($_POST['chunks'] + $_POST['rs']);
  if($nb > MAXQRCODES || $nb < 1){
    return json_error();
  }
  $size = (int)($_POST['size'] * 10);
  if($size < MINSIZE || $size > MAXSIZE){
    return json_error();
  }
  
  if(($_SESSION['datalength'] / $_POST['chunks']) > MAXDATA){
    return json_error();
  }
  $title = null;
  if(!is_null(get_array($_POST, 'checkbox', 'desc')) && isset($_POST['optiontitle'])){
    $title = (string) $_POST['optiontitle'];
  }
  
  $return = encode($_SESSION['data'], $_SESSION['sha1'], $_POST['chunks'], $_POST['rs'], $_POST['size']*10, !is_null(get_array($_POST, 'checkbox', 'count')), !is_null(get_array($_POST, 'checkbox', 'total')), $title);
  
  if($return !== true){
    return json_error(array(
        'msg' => $return
    ));
  }
  
  return json_valid();
}

function json_cb_get_status(){
  $content = get_state();
  if($content === false){
    $content = 'Please wait';
  }
  return json_valid(array(
      'msg' => $content
  ));
}

function json_cb_send_decode(){
  sleep(2);
  $raw = getencodedata($_POST[$_GET['num']], 'file');
  if($raw == false){
    return json_error('file error');  
  }
  if (!isset($_SESSION['decode_size'])) {
    $_SESSION['decode_size'] = 0;  
  }
  $_SESSION['decode_size'] += strlen($raw);
  if($_SESSION['decode_size'] > MAXDECODE){
    return json_error('Too much data ><');
  }
  $_SESSION['decode_img'][$_GET['num']] = $raw;
  trigger_error(count($_SESSION['decode_img']));
  session_write_close();
  return json_valid();
}

function json_cb_get_decode() {
  sleep(2);
  $_SESSION['tmpdir'] = sha1(gen_key(32)); 
  $return = decode($_SESSION['decode_img'], $_SESSION['tmpdir']);
  if($return !== true){
    return json_error($return);
  }
  return json_valid();
}





?>