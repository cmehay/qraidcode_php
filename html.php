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
      <script type="text/javascript">(function(){
        window.maxqrcodes = '.MAXQRCODES.';
        window.maxlength_encode = '.MAXINPUT.';
        window.maxlength_decode = '.MAXDECODE.';
      })()
      </script>
      '.$script_var.'
    </head>';
  $body='<body><div id="whole">'.$content.'</div> </body></html>';
  return $header.$body;
}

function html_about(){
  return
 '<div class="question">What is QRaidCODE?</div>
  <div class="answer">QRaidCODE is a system allowing to write data on multiple Qrcodes with parity which allows you to decode your data with a fraction of the qrcodes generated.</div>
  <div class="question">How does it work?</div>
  <div class="answer">The data are packed and encrypted, the same Reed-Solomon algorythm involved in RAID6 is used for the parity system.</div>
  <div class="question">Is this secure?</div>
  <div class="answer">Yep, the data are encrypted and the encryption key is distributed on the Qrcodes. If someone found one of the qrcode, he will not be able to read the content,
			all qrcodes are required to get the data and the encryption key.</div>
  <div class="question">How should I use the parity system?</div>
  <div class="answer">The parity system allows you to get more qrcodes than needed to decode your data. For instance, if you generate 10 qrcodes with data and 4 qrcodes with parities,
			you will be able to decode your data using 10 out of the 14 qrcodes. It should be usefull if you lost some.</div>
  <div class="question">Is QRaidCode opensource?</div>
  <div class="answer">Not yet, but I am working on a javascript porting which will be under a Free licence and Opensource.</div>
  <div class="question">Can I use another qrcode reader to read the qrcodes?</div>
  <div class="answer">Nope, you can read the qrcodes with any reader, but you will not be able to decode them.</div>
  <div class="question">How many qrcodes can I create?</div>
  <div class="answer">The Reed-Solomon algorythm used in QRaidCODE can compute up to 256 qrcodes, but for performance issue, this demo allows you to create 150 qrcodes max.
			By the way, you can mathematically stock 740,096 bytes in 256 qrcodes with no parity, but again, for performance issue, this demo allows you to stock 300,000 bytes.</div>
  <div class="question">Would you want to thank someone?</div>
  <div class="answer">You do well in speaking of it. I would like to thank <a href="http://web.eecs.utk.edu/~plank/">James S. Plank</a> for his papers on Galois fields and Reed-Solomon algorythm,
			<a href="http://fukuchi.org/">Kentaro Fukuchi</a> for his tool <a href="http://fukuchi.org/works/qrencode/">qrencode</a> and for kindly sending me a useful patch for <a href="http://sourceforge.net/apps/mediawiki/zbar/index.php">zbarimg</a>,
			and my boyfriend who helped me to understand how to compute matrix and for proofread my english.</div>

  <div class="answer">I will publish more informations about this project on <a href="https://goldy.furry.fr/">my blog</a>.</div>

  <div class="answer">Please donate bitcoins to support this project if you like it : 1<span class="bold">Go1dy</span>1GRBAHbPUTu6xPaWPSqQPd4DzU2i</div>
  ' ;
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
  header('Content-Disposition: attachment; filename="'.urlencode('QRaidCODE.zip').'"');
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
  header('Content-Disposition: attachment; filename="'.urlencode('qraidcode_'.$_SESSION['filename']).'"');
  return $file;
}
