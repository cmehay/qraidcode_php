<?php
//html

function render($content){
  $script_var = null;
  if (is_array($_SESSION['conf']['scripts'])) {
    foreach($_SESSION['conf']['scripts'] as $file) {
	$script_var .= '<script src="'.$file.'"></script>';
    }
  }
    $header= 
    '<!doctype html>
    <html lang="'.$_SESSION['conf']['lang'].'">
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
      <meta name="viewport" content="width=device-width, initial-scale=1"/>
      <title>'.$_SESSION['conf']['title'].'</title>
      <link rel="stylesheet" href="styles/normalize.css">
      <link rel="stylesheet/less" type="text/css" href="styles/styles.less" />
      <script type="text/javascript">function cur_lang(){return "'.$_SESSION['conf']['lang'].'";}</script>
      '.$script_var.'
    </head>';
  $body='<body><div id="whole">'.$content.'</div> </body></html>';
  return $header.$body;
}

function html_cb_about(){
  return render(
 '<div id="title">About QRaidCODE</div>
  <div id="desciption">Coming soon...</div>') ;
}

function file_cb_getarchive(){
  if(!isset($_SESSION['archive'])){
    return 'File error :(';
  }
  $file = file_get_contents($_SESSION['archive']);
  if($file === false){
    return 'File error :(';  
  }
  header('Content-type: application/zip');
  header('Content-Disposition: attachment; filename="'.urlencode($_SESSION['filename']).'.zip"');
  return $file;
}

function file_cb_getpdf(){
  if(!isset($_SESSION['pdf'])){
    return 'File error :(';
  }
  $file = file_get_contents($_SESSION['pdf']);
  if($file === false){
    return 'File error :(';  
  }
  header('Content-type: application/pdf');
  header('Content-Disposition: attachment; filename="'.urlencode('qraidcode_'.$_SESSION['filename']).'.pdf"');
  return $file;
}



?>