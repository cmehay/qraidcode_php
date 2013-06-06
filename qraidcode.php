<?php

//session_start();

//test config
// if(!defined('QRENCODE') && !defined('ZBARIMG') && !defined('MATRIX')) {
// define('QRENCODE', 'qrencode');
// define('ZBARIMG', 'zbar-code/zbarimg/zbarimg');
// }


//matrix dir
//define('MATRIX', 'matrix/');


function base($set = null){
  if(!is_null($set) && !defined('BASE')){
    define('BASE', $set);
  }
  return BASE;
}

function set_base($n){
  $bases = array(
      8 => pow(2, 8),
      16 => pow(2, 16)
      //pas encore utilisable :(
      //32 => pow(2, 32)
  );
  foreach($bases as $key => $value) {
    if($n < $value){
      return base($key);  
    }
  }
  return false;
}

//générateur de table logarithmique de champs galois qui pète sa race

function set_table() {
  $prim_poly = array(
      4 => 023,
      8 => 0435,
      16 => 0210013,
      32 => 020000007
  );
  $base = base();
  $x_to_w = 1 << $base;
  $b = 1;
  for($log = 0; $log < $x_to_w-1; $log++){
    $gflog[$b] = $log;
    $gfilog[$log] = $b;
    $b = $b << 1;
    if($b & $x_to_w){
      $b = $b ^ $prim_poly[$base];
    }
  }
   return array(
       'gflog' => $gflog,
      'gfilog' => $gfilog
   );
  //return $gflog;
}

function set_table_global() {
  if(!isset($GLOBALS['table'])){
    $GLOBALS['table'] = set_table();  
  }
  return $GLOBALS['table'];
}

 
function mul($a, $b){
  $table = set_table_global();
  //var_dump(67);
  $base = base();
  $NW = 1 << $base;
  if($a == 0 || $b == 0){return 0;}
  $sum_log = $table['gflog'][$a] + $table['gflog'][$b];
  if($sum_log >= $NW-1){
    $sum_log -= $NW-1;
  }
  return $table['gfilog'][$sum_log];
}

function div($a, $b) {
  $table = set_table_global();
  //var_dump(78);
  $base = base();
  $NW = 1 << $base;
  if($a == 0){return 0;}
  if($b == 0){return -1;}
  $diff_log = $table['gflog'][$a] - $table['gflog'][$b];
  if($diff_log < 0){
    $diff_log += $NW-1;
  }
  return $table['gfilog'][$diff_log];
}

function expon($a, $e) {
  $b = $a;
  if($e == 0){return 1;}
  if($e == 1){return $a;}
  for($i=1;$i<$e;$i++) {
      $b=mul($b, $a);
  }
  return $b;
}

function set_matrix($c, $l){
  if (isset($GLOBALS['matrix'])) {
    return $GLOBALS['matrix'];
  }
  if(file_exists(MATRIX.$c.'/'.$l)){
    //var_dump('c bon');
    $GLOBALS['matrix'] = json_decode(bzdecompress(file_get_contents(MATRIX.$c.'/'.$l)));
    return $GLOBALS['matrix'];
  }
  
  

  for($i=0;$i<$c;$i++) {
    //var_dump($i);
    for($y=0;$y<$l;$y++) {
      $table[$i][$y]=expon($y, $i);  
    }
  }

  for($i=1;$i<$c;$i++) {
    //var_dump($i);
    if($i == 1){
      for($row=1;$row<$l;$row++) {
        for($col=0;$col<$c;$col++) {
	  if($col != 1){
	    $table[$col][$row] = $table[$col][$row] ^ $table[1][$row];
	  }
	} 
      }
    }
    else{
      $div=div(1, $table[$i][$i]);
      for($row=$i;$row<$l;$row++) {
	$table[$i][$row] = mul($div, $table[$i][$row]);	
      }
      for($col=0;$col<$c;$col++) {
	$mul= $table[$col][$i];
	for($row=$i;$row<$l;$row++) {
	  if($col != $i){
	    $table[$col][$row] = $table[$col][$row] ^ mul($mul, $table[$i][$row]);
	  }
	}
	

      }
    }
  }  
  

  //inverse les lignes et les colonnes
  foreach($table as $col => $tmp){
    foreach($tmp as $row => $value) {
      //var_dump($row.'_'.$col);
      $tmptable[$row][$col] = $table[$col][$row];  
    } 
  }
  //print_r('set_matrix: ');
  //print_r($table);
  //$GLOBALS['matrix']= $tmptable;
  return $tmptable;

}




 

function crypto_rand($min,$max){
  $range = $max - $min;
  if ($range == 0) return $min; // not so random...
  $log = log($range, 2);
  $bytes = (int) ($log / 8) + 1; // length in bytes
  $bits = (int) $log + 1; // length in bits
  $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
  do {
      $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
      $rnd = $rnd & $filter; // discard irrelevant bits
  } while ($rnd >= $range);
  return $min + $rnd;
}

//print_r($table);

//print_r(mul(13, 10, $table, array_flip($table)));
function set_maxlength($base){
  $maxlegth = array(
      8 => 2891,
      16 => 2919,
      32 => 2913
  );  
  return $maxlegth[$base];
}

// function split_data_file($file, $n, $p){
//   $base=set_base($n+$p);
//   $maxlen=set_maxlength($base);
//   //pas plus de 2go s'il te plaît
//   $strlen=filesize($file);
//   $modulo=$strlen % $n;
//   if($modulo != 0){
//     $add = $n - $modulo;
//   }else {
//     $add = 0;  
//   }
//   //test le multiple de la longueur des chunks
//   $chunklen=($strlen+$add)/$n;
//   if($chunklen % ($base/8) !== 0){
//     $more = (($base/8) - ($chunklen % ($base/8)));
//     $add += $more * $n;
//     $chunklen += $more;
//   } 
//   $f=fopen($file,'a');
//   if($add != 0){
//     for($i=$strlen;$i<$strlen+$add;$i++) {
//       fwrite($f, pack('C*', crypto_rand(0, 255)));
//     }
//   }
//   $s=0;
//   if($chunklen > $maxlen){return 'error, too long';}
//   
//   for($i=0; $i<$n; $i++){
//     $chunk = fopen(WORKDIR.'DATA_'.$i);
//     for($k=$s;$k<$s+$chunklen;$k+$s+$chunklen) {
//       //$data[$i] .=  $str[$k];
//       
//     }
//     
// 
//     $s+=$chunklen;
//   }
//   
// }



function split_data($str, $n, $p){
  $base=set_base($n+$p);
  $maxlen=set_maxlength($base);
  $strlen=strlen($str);
  $modulo=$strlen % $n;
  if($modulo != 0){
    $add = $n - $modulo;
  }else {
    $add = 0;  
  }
  //test le multiple de la longueur des chunks
  $chunklen=($strlen+$add)/$n;
  if($chunklen % ($base/8) !== 0){
    $more = (($base/8) - ($chunklen % ($base/8)));
    $add += $more * $n;
    $chunklen += $more;
  }
  
  
  if($add != 0){
    for($i=$strlen;$i<$strlen+$add;$i++) {
      $str[$i] = pack('C*', crypto_rand(0, 255)) ;
    }
    //$str = str_pad($str, $strlen+$add, "\x00", STR_PAD_RIGHT);  
  }
  
//   var_dump($strlen);
//   var_dump($modulo);
//   var_dump($add);
//   var_dump($chunklen);
//   var_dump(strlen($str));
  //$lastchunklen=($strlen-($chunklen*($n-1)));
  $s=0;
  //print(($chunklen*($w-1)) + $lastchunklen.' = '.$strlen);
  //echo $lastchunklen;
  if($chunklen > $maxlen){return 'error, too long';}  
  for($i=0; $i<$n; $i++){
    $data[$i] = null;
    for($k=$s;$k<$s+$chunklen;$k++) {
      $data[$i] .=  $str[$k]; 
    }
    
    //$data[$i]= substr($str, $s, $chunklen);
    //if($i === $n-1 && $lastchunklen < $chunklen){
      //remplie avec du vide normalement
      //$data[$i] = str_pad($data[$i], $chunklen, "\x00", STR_PAD_RIGHT);
    //}
    $s+=$chunklen;
    //var_dump(strlen($data[$i]));
  }
  return array(
      'data' => $data,
      'length' => $strlen,
      'chunks' => $n
  );
}

function reed_solomon_enc_16($array, $m){
  $c = count($array['data']);
  if($c+$m > 65536){return false;}
  $table=set_matrix($c, $m+$c);
  $chunklen = strlen($array['data'][0]);
  
  //supprime la matrice identité  
  for($i=0;$i<$c;$i++) {
    unset($table[$i]);
  }
  $table = array_merge($table);
  
  //print_r($table);
  for($i=0;$i<$chunklen;$i+=2) {
    //var_dump($i);
    for($y=0;$y<$m;$y++) {
      if(!isset($rs[$y])){
	$rs[$y]=null;
      }
      foreach($array['data'] as $key => $value) {
	$unpack=unpack('v', $value[$i].$value[$i+1]);
	//var_dump($unpack);
        $xor[$key] = pack('v', mul($table[$y][$key], $unpack[1]));

        if($key > 0){
	  $xor[0] = $xor[0] ^ $xor[$key];
        }  
      }
      //print_r($xor[0]);
      $rs[$y] .=  $xor[0]; 
      //var_dump(strlen($rs[$y]));
    }
  }
  return $rs;    
}

function reed_solomon_dec_16($data, $rs, $chunks, $length=null, $build=false){
  $c=$chunks;
  $chunklen = strlen($data[array_rand($data)]);
  end($rs);$maxrs = key($rs);reset($rs);
  $l=$c+$maxrs+1;
  $avail = count($data);
  
  if($avail + count($rs) < $c){
    return false;
  }

  $tmptable=set_matrix($c, $l);
  
  //réduit la matrice
  for($row=0;$row<$c;$row++){
    if(isset($data[$row])){
      $data_vector[$row] = $data[$row];
      $newtable[$row] = $tmptable[$row];
    }else{
      if(!isset($data_vector[$row])){
	foreach($rs as $key => $value){
	  if(!isset($last)){
	    $data_vector[$row] = $rs[$key];
	    $last=$key;
	    break;
	  }else{
	    if($key > $last){
	      $data_vector[$row] = $rs[$key];
	      $last=$key;
	      break;
	    }
	  }
	}  
      }	
      $newtable[$row] = $tmptable[$last+$c];
    }
  }
 
  
//   print_r($data_vector);
// 
//   print_r($newtable);
  
  //inversion de la table
  $newtable = inverse($newtable);
  
  //print_r($newtable);
  //reconstruction
  for($disk=0;$disk<$c;$disk++) {
    //var_dump($disk);
    if(!isset($data[$disk])){
      $data[$disk]=null;
      for($byte=0;$byte<$chunklen;$byte+=2) {
	for($n=0;$n<$c;$n++) {	
	  //for test only//
// 	  $unpack=(int) $data_vector[$n];
	  //var_dump($unpack);
// 	  var_dump($newtable[$disk][$n]);
// 	  $xor[$n] = mul($unpack, $newtable[$disk][$n]);
	  /////////////////
	  
	  $unpack=unpack('v', $data_vector[$n][$byte].$data_vector[$n][$byte+1]);
	  $xor[$n] = pack('v', mul($unpack[1], $newtable[$disk][$n]));  	
	  if($n > 0){
	    $xor[0] = $xor[0] ^ $xor[$n];
	  }
        }
        $data[$disk] .= $xor[0];
      }
    }
  }
  ksort($data);
  if(!$build && !is_null($length)){
    return $data;
  }
  $return = null;
  foreach($data as $value) {
    $return .= $value;  
  }
  return substr($return, 0, $length);
}


function reed_solomon_enc_8($array, $m){
//   foreach($array['data'] as $key => $value){
//     for($i=0;$i<$m;$i++){
//       $table[$key][$i]=expon($i+1, $key);  
//     }
//   }
  $c = count($array['data']);
  if($c+$m > 256){return false;}
  $table=set_matrix($c, $m+$c);
  $chunklen = strlen($array['data'][0]);
  //var_dump($chunklen);
  //supprime la matrice identité  
  for($i=0;$i<$c;$i++) {
    unset($table[$i]);
  }
  $table = array_merge($table);
  
  //print_r($table);
  for($i=0;$i<$chunklen;$i++) {
    //var_dump($i);
    for($y=0;$y<$m;$y++) {
      if(!isset($rs[$y])){
	$rs[$y]=null;
      }
      foreach($array['data'] as $key => $value) {
	$unpack=unpack('C*', $value[$i]);
	//var_dump($unpack);
        $xor[$key] = pack('C*', mul($table[$y][$key], $unpack[1]));

        if($key > 0){
	  $xor[0] = $xor[0] ^ $xor[$key];
        }  
      }
      //print_r($xor[0]);
      $rs[$y] .=  $xor[0]; 
      
      //var_dump(strlen($rs[$y]));
    }
  }
  return $rs;
}

function reed_solomon_dec_8($data, $rs, $chunks, $length=null, $build=false){
  $c=$chunks;
  $chunklen = strlen($data[array_rand($data)]);
  end($rs);$maxrs = key($rs);reset($rs);
  $l=$c+$maxrs+1;
  $avail = count($data);
  
  if($avail + count($rs) < $c){
    return false;
  }

  $tmptable=set_matrix($c, $l);
  
  //réduit la matrice
  for($row=0;$row<$c;$row++){
    //var_dump($row);
    if(isset($data[$row])){
      $data_vector[$row] = $data[$row];
      $newtable[$row] = $tmptable[$row];
    }else{
      if(!isset($data_vector[$row])){
	foreach($rs as $key => $value){
	  if(!isset($last)){
	    $data_vector[$row] = $rs[$key];
	    $last=$key;
	    break;
	  }else{
	    if($key > $last){
	      $data_vector[$row] = $rs[$key];
	      $last=$key;
	      break;
	    }
	  }
	}  
      }	
      $newtable[$row] = $tmptable[$last+$c];
    }
  }
 
  
//   print_r($data_vector);
// 
//   print_r($newtable);
  
  //inversion de la table
  $newtable = inverse($newtable);
  
  //print_r($newtable);
  //reconstruction
  for($disk=0;$disk<$c;$disk++) {
    //var_dump($disk);
    if(!isset($data[$disk])){
      $data[$disk]=null;
      for($byte=0;$byte<$chunklen;$byte++) {
	for($n=0;$n<$c;$n++) {	
	  //for test only//
// 	  $unpack=(int) $data_vector[$n];
	  //var_dump($unpack);
// 	  var_dump($newtable[$disk][$n]);
// 	  $xor[$n] = mul($unpack, $newtable[$disk][$n]);
	  /////////////////
	  
	  $unpack=unpack('C*', $data_vector[$n][$byte]);
	  $xor[$n] = pack('C*', mul($unpack[1], $newtable[$disk][$n]));  	
	  if($n > 0){
	    $xor[0] = $xor[0] ^ $xor[$n];
	  }
        }
        $data[$disk] .= $xor[0];
      }
    }
  }
  ksort($data);
  if(!$build && !is_null($length)){
    return $data;
  }
  $return = null;
  foreach($data as $value) {
    $return .= $value;  
  }
  return substr($return, 0, $length);
}

function inverse($matrix) {
  $square=count($matrix);
  //merci princess-sarah :3
  //matrice identité
  foreach($matrix as $i => $tmp) {
    foreach($tmp as $y => $value) {
      if($i === $y){
	$invert[$i][$y] = 1;
      }
      else {
        $invert[$i][$y] = 0;  
      } 
    }
  }
    //déplier la matrice
  foreach($matrix as $i => $tmp) {
    foreach($tmp as $y => $value) {
      $tmpmatrix[] = $matrix[$i][$y];
      $tmpinvert[] = $invert[$i][$y];
    }
  }
  for($i=0;$i<$square;$i++) {
    $row_start = $square*$i;  
    
    if($tmpmatrix[$row_start+$i] == 0){
      for($j=$i+1;$j<$square && $tmpmatrix[$square*$j+$i] == 0;$j++);
      if($j == $square){
	//print_r('pas inversible');
        return false;  
      }
      $rs2= $j*$square;
      for($k=0;$k<$square;$k++) {
        $tmp = $tmpmatrix[$row_start+$k];$tmpmatrix[$row_start+$k] = $tmpmatrix[$rs2+$k];$tmpmatrix[$rs2+$k] = $tmp;  
        $tmp = $tmpinvert[$row_start+$k];$tmpinvert[$row_start+$k] = $tmpinvert[$rs2+$k];$tmpinvert[$rs2+$k] = $tmp;  
      }
    }
    $tmp = $tmpmatrix[$row_start+$i];
    if($tmp != 1){
      $inverse = div(1, $tmp);
      for($j=0;$j<$square;$j++){
        $tmpmatrix[$row_start+$j] = mul($tmpmatrix[$row_start+$j], $inverse);
        $tmpinvert[$row_start+$j] = mul($tmpinvert[$row_start+$j], $inverse);
      }
    }
    $k=$row_start+$i;
    for($j=$i+1;$j != $square;$j++){
      $k += $square;
      if($tmpmatrix[$k] != 0){
        if($tmpmatrix[$k] == 1){
          $rs2 = $square*$j;
          for($x=0;$x<$square;$x++){
            $tmpmatrix[$rs2+$x] ^= $tmpmatrix[$row_start+$x];
            $tmpinvert[$rs2+$x] ^= $tmpinvert[$row_start+$x];            
          }
        }else{
          $tmp = $tmpmatrix[$k];
          $rs2 = $square*$j;
          for($x=0;$x<$square;$x++){
            $tmpmatrix[$rs2+$x] ^= mul($tmp, $tmpmatrix[$row_start+$x]);  
            $tmpinvert[$rs2+$x] ^= mul($tmp, $tmpinvert[$row_start+$x]);  
          }
        } 
      }
    }
  }
  
  for($i=$square-1;$i >= 0;$i--){
    $row_start = $i*$square;
    for($j=0;$j<$i;$j++){
      $rs2 = $j*$square;
      if($tmpmatrix[$rs2+$i] != 0){
        $tmp = $tmpmatrix[$rs2+$i];
        $tmpmatrix[$rs2+$i] = 0;
        for($k=0;$k<$square;$k++){
          $tmpinvert[$rs2+$k] ^= mul($tmp, $tmpinvert[$row_start+$k]);  
        }
        
      }
      
    }
  }
  //   //replier la matrice
  foreach($matrix as $i => $tmpvalue) {
    foreach($tmpvalue as $y => $value) {
      $invert[$i][$y] = $tmpinvert[$i*$square+$y];   
    }
  }
  
  
  return $invert;
}

function gen_key($chunks){
  if($chunks < 32){
    return openssl_random_pseudo_bytes(32);
  }
  return openssl_random_pseudo_bytes($chunks);
}

function key_size($chunks){
  if($chunks < 32){
    return 32;
  }
  return $chunks;
}





function encrypt_data($data, $key=null) {
  
  $cryptkey = hash('sha256', $key, true);
  
  $iv = hash('sha256', $cryptkey, true);
  for($i=0;$i<42;$i++) {
    $iv = hash('sha256', $iv, true);
  }
  $cipher = mcrypt_module_open('rijndael-256', '', 'nofb', '');
  $iv = substr($iv, 0, mcrypt_enc_get_iv_size($cipher));
  if(mcrypt_generic_init($cipher, $cryptkey, $iv) != -1){
    $encrypted = mcrypt_generic($cipher, $data);
    mcrypt_generic_deinit($cipher);
  }
  return array(
    'data' => $encrypted,
    'key' => $key);
}

function decrypt_data($key, $data){

  $cryptkey = hash('sha256', $key, true);
  $iv = hash('sha256', $cryptkey, true);
  for($i=0;$i<42;$i++) {
    $iv = hash('sha256', $iv, true);
  }
  $cipher = mcrypt_module_open('rijndael-256', '', 'nofb', '');
  $iv = substr($iv, 0, mcrypt_enc_get_iv_size($cipher));
  if(mcrypt_generic_init($cipher, $cryptkey, $iv) != -1){
    $decrypted = mdecrypt_generic($cipher, $data);
    mcrypt_generic_deinit($cipher);
  }
  return $decrypted;
}

function format_enc($ver, $type, $count, $cur, $data, $length, $checksum, $key, $compact=true) {
  do {
    $delimit = openssl_random_pseudo_bytes(2); 
  } while(strpos($data, $delimit) !== false); 
  if($type == 'data'){$add = 1;}else{$add = 2;}
  $basetoversion=array(
      8 => 1,
      16 => 2
  );
  switch ($basetoversion[$ver]) {
    case 1:
      //version 1
      $struct=array(
        //version + type
       0 => pack('C', $basetoversion[$ver]*10 +$add),
        
        //nb de chunks
       1 => pack('C', $count),
        
        //current chunklen
       2 => pack('C', $cur),
        
        //delimiteur
       3 => $delimit,
        
        //données
       4 => $data,
        
        //délimiteur
       5 => $delimit,
        
        //crc32
       6 => $checksum,
        
        //data lenght
       7 => $length,
      
        //clé de chiffrement
       8 => $key
      );  
    break;
    
    case 2:
      //version 2
      $struct=array(
        //version + type
       0 => pack('C', $basetoversion[$ver]*10 +$add),
        
        //nb de chunks
       1 => pack('v', $count),
        
        //current chunklen
       2 => pack('v', $cur),
        
        //delimiteur
       3 => $delimit,
        
        //données
       4 => $data,
        
        //délimiteur
       5 => $delimit,
        
        //crc32
       6 => $checksum,
        
        //data lenght
       7 => $length,
      
        //clé de chiffrement
       8 => $key
      ); 
    break;

//cette version à besoin de trop de ram pour être utilisable :(    
//     case 3:
//       //version 3
//       $struct=array(
//         //version + type
//        0 => pack('C', $ver*10 +$add),
//         
//         //nb de chunks
//        1 => pack('V', $count),
//         
//         //current chunklen
//        2 => pack('V', $cur),
//         
//         //delimiteur
//        3 => $delimit,
//         
//         //données
//        4 => $data,
//         
//         //délimiteur
//        5 => $delimit,
//         
//         //crc32
//        6 => $checksum,
//         
//         //data lenght
//        7 => $length,
//       
//         //clé de chiffrement
//        8 => $key
//       ); 
//     break;
    
      
  }  
  if(!$compact){
    return $struct;
  }
  $return = null;
  foreach($struct as $value){
    $return .= $value;  
  }
  return $return;
}

function format_dec($data) {
  $version = floor(array_shift(unpack('C', $data[0])) / 10);
  switch ($version) {
    case 1:
      //version 1
      $typecode = array_shift(unpack('C', $data[0])) - ($version * 10);
      if($typecode == 1){
        $return['type'] = 'data';  
      }else {
        $return['type'] = 'rs';    
      }
      //nombre de chunks
      $return['count'] = array_shift(unpack('C', $data[1]));
      
      //current chunk
      $return['current'] = array_shift(unpack('C', $data[2]));
      
      //séparateur
      $sep = $data[3].$data[4];
      //var_dump(bin2hex($sep));
      $pos = strpos($data, $sep, 5);
      if($pos === false){
	//print_r('not valid');
        return false;  
      }
      //données
      $return['data'] = substr($data, 5, $pos-5);
      //var_dump(bin2hex($data[$pos].$data[$pos+1]));
      //var_dump(strlen($return['data']));
      $next = $pos+2;
      
      //crc32
      $return['checksum'] = $data[$next].$data[$next+1].$data[$next+2].$data[$next+3];
      //var_dump(bin2hex($return['checksum']));
      //lenght
      $return['crypted_length'] = $data[$next+4].$data[$next+5].$data[$next+6].$data[$next+7];
      //var_dump($return['crypted_length']);
      //clé
      $return['key'] = substr($data, $next+8);
    break;
    
    
    case 2:
      //version 2
      $typecode = array_shift(unpack('C', $data[0])) - ($version * 10);
      if($typecode == 1){
        $return['type'] = 'data';  
      }else {
        $return['type'] = 'rs';    
      }
      //nombre de chunks
      $return['count'] = array_shift(unpack('v', $data[1].$data[2]));
      
      //current chunk
      $return['current'] = array_shift(unpack('v', $data[3].$data[4]));
      
      //séparateur
      $sep = $data[5].$data[6];
      //var_dump(bin2hex($sep));
      $pos = strpos($data, $sep, 7);
      if($pos === false){
	//print_r('not valid');
        return false;  
      }
      //données
      $return['data'] = substr($data, 7, $pos-7);
      //var_dump(bin2hex($data[$pos].$data[$pos+1]));
      //var_dump(strlen($return['data']));
      $next = $pos+2;
      
      //crc32
      $return['checksum'] = $data[$next].$data[$next+1].$data[$next+2].$data[$next+3];
      //var_dump(bin2hex($return['checksum']));
      //lenght
      $return['crypted_length'] = $data[$next+4].$data[$next+5].$data[$next+6].$data[$next+7];
      //var_dump($return['crypted_length']);
      //clé
      $return['key'] = substr($data, $next+8);
    break;
      
  }
  return $return;
}

function retreive_data($data){
  foreach($data as $value) {
    if(!isset($last)){
      $last['checksum'] = $value['checksum'];
      $last['count'] = $value['count'];
      $last['crypted_length'] = $value['crypted_length'];
    }else {
      if($last['checksum']       !== $value['checksum'] ||
	 $last['count']    	 !== $value['count'] ||
	 $last['crypted_length'] !== $value['crypted_length']){
	//reject this chunk 
	//print_r('là?');
        continue;  
      }
    }
    //print_r($value);
    
    $parse['data'][$value['type']][$value['current']] = $value['data'];
    $parse['key'][$value['type']][$value['current']] = $value['key'];
  }
  //print_r($parse);
  $chunks_num = count($parse['data']['data']);
  if(isset($parse['data']['rs'])){
    $rs_num = count($parse['data']['rs']);  
  }else {
    $rs_num = 0;  
  }
  
  if($chunks_num + $rs_num < $last['count']){
    return false;
  }
  $reed_solomon_dec = 'reed_solomon_dec_'.base();
  
  $keylen = key_size($last['count']);
  
  $key = $reed_solomon_dec($parse['key']['data'], $parse['key']['rs'],$last['count'], $keylen, true);
  if(!$key){return false;}
  $data = $reed_solomon_dec($parse['data']['data'], $parse['data']['rs'],$last['count'], array_shift(unpack('L', decrypt_data($key, $last['crypted_length']))), true);
  if(!$data){return false;}
  return decrypt_data($key, $data);
}

function qrencode($data){
  exec('echo "'.base64_encode($data).'" | base64 -d | "'.QRENCODE.'" -8 -s 1 -m 0 -o - | base64 -w 0', $qrcode, $return);
  //var_dump(base64_encode($data));
  if($return != 0 && !$qrcode){
    return false;  
  }
  return base64_decode($qrcode[0]);
}

function get_file_type($picture, $option='--mime-type'){
  exec('echo "'.base64_encode($picture).'" | base64 -d | file '.$option.' -b -', $mime, $return);
  if($return != 0 || !$mime){
    return false;  
  }
  $mime = explode('/', $mime[0]);
  if($mime[0] !== 'image'){return false;};
  return $mime[1];
}

function qrdecode($picture){
  try {
    $img = new Imagick();
    $img->setFormat(get_file_type($picture));
    $img->readImageBlob($picture);
    $img->setFormat('MIFF');      
  }catch(Exception $e){
    trigger_error($e);
    return false;
  }

  $descriptorspec = array(
    0 => array("pipe", "r"),  // // stdin est un pipe où le processus va lire
    1 => array("pipe", "a"),  // stdout est un pipe où le processus va écrire
    2 => array("pipe", "a") // stderr est un fichier
  );
  
  $process = proc_open('"'.ZBARIMG.'" -q MIFF:-', $descriptorspec, $pipes, '/var/www/bin', null);
  
  if (!is_resource($process)) {
    trigger_error('not ressource');
    return false;
  }
  fwrite($pipes[0], $img);
  trigger_error('ici');
  $decoded =  stream_get_contents($pipes[1]);
  trigger_error('ici');
  $stderr = stream_get_contents($pipes[2]);
  trigger_error('ici');
  fclose($pipes[0]); fclose($pipes[1]); fclose($pipes[2]);
  if(proc_close($process) != 0){
    trigger_error($decoded);
    trigger_error($stderr);
    return false;  
  }
  $data = explode('QR-Code:', $decoded);
  unset($decoded);
  //var_dump($xml[0]);
  //$data = xmlstr_to_array(base64_decode($xml[0]));
  //exec('echo "'.base64_encode($picture).'" | base64 -d > test.png');
  //var_dump(base64_decode($xml[0]));
  //var_dump($data);
  //var_dump(base64_encode($data[1]));
  //var_dump(base64_encode($data[2]));
  //var_dump(base64_encode(base64_decode($data['source']['index']['symbol']['data']['@content'])));
  array_shift($data);
  foreach($data as $key => $value){
    $data[$key]=base64_encode($value);
    $length = strlen($data[$key]);
    //fix the last char
    $data[$key][$length-1] = '=';
    $data[$key] = base64_decode($data[$key]);
  }
  //var_dump($data[1]);
  return $data;
}

function archive_create($qrcodes, $sha1){
  try{
    $zip = new ZipArchive;
    $zip->open(WORKDIR.$sha1.'/'.$sha1.'.zip', ZipArchive::CREATE);
    trigger_error(WORKDIR.$sha1.'/'.$sha1.'.zip');
    foreach($qrcodes as $key => $value){
      $zip->addFile($value, ($key+1).'.png');
      trigger_error($value);
      //unlink($value);
      trigger_error($key);
    }
    $zip->close();
  }catch(Exception $e) {
    trigger_error('Imagick caught exception: ' . $e->getMessage());
    return false;     
  }
  $_SESSION['archive'] = WORKDIR.$sha1.'/'.$sha1.'.zip';
  return true;
}

function custom_qrcodes($qrcodes, $nbdata, $tmpdir, $num=false, $required=false, $name=null){
  if(!$num && !$required && is_null($name)){
    foreach($qrcodes as $key => $value){
      file_put_contents(TMPDIR.'/pre_'.$key.'.png', $value);  
      $qrcodes[$key] = TMPDIR.'/pre_'.$key.'.png';
    }
    return $qrcodes;
  }
  $qrsize = imagesx(imagecreatefromstring($qrcodes[0]));
  if($qrsize === false){
    return false;
  }
  foreach($qrcodes as $key => $value) {
    try {
    $img = new Imagick();
    //$color = new ImagickPixel();
    $draw = new ImagickDraw();
    $draw->setFont(FONT);
    $draw->setFontSize( 16 );
    $img->setFormat('PNG8');
    $img->readImageBlob($value);
    if($num){
      $img->annotateImage($draw, 16, 16, 0, ($key+1));
    }
    if($required){
      $str = $nbdata.' are required';
      $offset = ($qrsize) - ((strlen($str) * 16) + 16);
      $img->annotateImage($draw, $offset, 16, 0, $str);
      unset($offset);
    }
    if(!is_null($name)){
      $str = explode( "\n", wordwrap( $name, ($qrsize*16)-16));
      $offset[0] = (($qrsize) - (strlen($str[0]) * 16) + 16)/2;
      $img->annotateImage($draw, $offset[0], $qrsize - (16*3), 0, $str[0]);
      if(isset($str[1])){
	$offset[1] = (($qrsize) - (strlen($str[1]) * 16) + 16)/2;  
	$img->annotateImage($draw, $offset[1], $qrsize - (16 + (16/2)), 0, $str[1]);
      }
    }
    //$img->setImageDepth(8);
    //$img->setImageAlphaChannel(imagick::ALPHACHANNEL_DEACTIVATE);
    //$img->setImageChannelDepth(imagick::CHANNEL_GRAY, 1);
    $img->setImageColormapColor(0, 'white');
    $img->setImageColormapColor(1, 'black');
    $img->setImageCompressionQuality(00);
    if(!is_dir(TMPDIR)){
      mkdir(TMPDIR);
    }
    $img->writeImage(TMPDIR.'/pre_'.$key.'.png');
    unset($img);
    }catch(Exception $e) {
     trigger_error('Imagick caught exception: ' . $e->getMessage());
     return false;  
    }
    
    $qrcodes[$key] = TMPDIR.'/pre_'.$key.'.png';
    
  }
  return $qrcodes;
}

// function optimize_png($qrlist, $tmpdir){
//   foreach($qrlist as $key => $prepng) {
//     $postpng = TMPDIR.'/'.$key.'.png';
//     exec(PNGCRUSH.' -bit_depth 1 -plte_len 2  -q '.$prepng.' '.$postpng);
//     unlink($prepng);
//     $qrlist[$key] = $postpng;
//   }
//   return $qrlist;
//   
// }

function pdf_create($qrcodes, $sha1, $nbdata, $size, $num=false, $required=false, $name=null){
  //calcul des proportions
  $x=210;
  $y=297;  
  $margin=10;
  $numqrcode=count($qrcodes);
  $xqrnb = floor(($x-($margin*2)) / $size);
  $incx = $xqrnb;
  $yqrnb = floor(($y-($margin*2)) / $size);
  $incy = $yqrnb;
  $qrperpage = $xqrnb * $yqrnb;
  $pagesnb = ceil($numqrcode/$qrperpage);

  $initx = floor(($x - ($xqrnb * $size))/2); 
  $inity = floor(($y - ($yqrnb * $size))/2);
  $innermargin = 10;// by 2
  if(!is_null($name)){
    text_to_png($name, 'title', TMPDIR);
    $titlesize = getimagesize(TMPDIR.'/'.'title.png');
    if(($titlesize[0] / $titlesize[1]) * ($innermargin / 2) > ($size - $innermargin)){
      $titlex = ($size - $innermargin);
      $titley = ($titlesize[1] / $titlesize[0]) * $titlex;
    }else{
      $titley =  $innermargin / 2;
      $titlex = ($titlesize[0] / $titlesize[1]) * $titley;
    } 
    $titleoffsetx = round(($size - $titlex) /2);
    $titleoffsety = round(($size - $innermargin) + (($innermargin - $titley)/2));
    trigger_error($titlex);
    trigger_error($titley);
    
  }
  

  
  
  //num offset
  $numsize= $innermargin/2;
  $numoffset = ($innermargin - $numsize ) /2;
  
  //required offset
  if($required){
    $reqsize = getimagesize(PNGDIR.'required.png');
    $reqnumsize = getimagesize(PNGDIR.$nbdata.'.png');
    $reqsizey = round($innermargin/3);
    $reqsizex = round(($reqsize[0] / $reqsize[1]) * $reqsizey);
    $reqoffsetx = round(($size - ($margin / 4)) - $reqsizex);
    $reqoffsety = round(($innermargin - $reqsizey ) /2);
    
    $requirednumoffset = round(($reqnumsize[0] / $reqnumsize[1]) * $reqsizey) + 2;
    //trigger_error($numoffset);
  }  

  

  
  try{
    $pdf = new tFPDF('P','mm','A4');
    $pdf->SetDrawColor(0);
  //   $posx = $initx;
  //   $posy = $inity;
    $current = 0;
    for($page=0;$page<$pagesnb;$page++) {
      $pdf->addPage();
      $offsetx=$initx;
      $offsety=$inity;
      $xqrnb = $incx;
      for($code=0;$code<$qrperpage;$code++) {
	if($code == $xqrnb){
	  $offsetx = $initx;
	  $offsety += $size;
	  $xqrnb += $incx;
	}
	//$pdf->SetXY($offsetx, $offsety);
	//dessine le cadre
	$pdf->Rect($offsetx, $offsety, $size, $size);
	//ajoute le numéro du qrcode
	if($num){
	  $pdf->Image(PNGDIR.($current+1).'.png', $offsetx+$numoffset, $offsety+$numoffset, 0, $numsize);  
	}
	//indique le nombre de requis
	if($required){
	  $pdf->Image(PNGDIR.'required.png', $offsetx+$reqoffsetx, $offsety+$reqoffsety, 0, $reqsizey);
	  $pdf->Image(PNGDIR.$nbdata.'.png', $offsetx+$reqoffsetx-$requirednumoffset, $offsety+$reqoffsety, 0, $reqsizey);
	}
	//ajoute le titre
	if(isset($titlesize)){
	  $pdf->Image(TMPDIR.'/'.'title.png', $offsetx+$titleoffsetx, $offsety+$titleoffsety, 0, $titley);
	}
	//ajouter le qrcode
	file_put_contents(TMPDIR.'/'.$current.'.png', $qrcodes[$current]);
	$pdf->Image(TMPDIR.'/'.$current.'.png', $offsetx+$margin, $offsety+$margin, 0, $size - ($margin * 2));
	unlink(TMPDIR.'/'.$current.'.png');
	$qrcodes[$current];
	$current++;
	$offsetx += $size;
	if(!isset($qrcodes[$current])){
	  break;
	}
      }
    }
    $pdf->Output(TMPDIR.'/'.$sha1);
  }catch(Exception $e) {
     trigger_error('fpdf causes exception: ' . $e->getMessage());
     return false;
  }
  return true;
}


function matrix_gen(){
  //génère les matrices et les écrits dans des fichiers
  $base(8);

  for($i=1;$i<257;$i++) {
    for($n=256-$i;$n>=0;$n--){
    $table = set_table();
    $matrix = set_matrix($i, $i+$n);
    if(!is_dir('php/matrix/'.$i)){
      mkdir('php/matrix/'.$i);  
    }
    $error = file_put_contents('php/matrix/'.$i.'/'.($i+$n), bzcompress(json_encode($matrix)));
    //var_dump('php/matrix/'.$i.'/'.$i+$n);
    }
  }

}

function text_to_png($txt, $name, $dir=WORKDIR){
  $size = 16;
  $len = (int)(strlen($txt)*16);
  $img = new Imagick();
  $pixel = new ImagickPixel( 'white' );
  $draw = new ImagickDraw();
  $draw->setFont(FONT);
  $draw->setFontSize( 16 );
  $draw->setFillColor('black');
  $img->newImage($len, 32, $pixel);
  $img->annotateImage($draw, 0, 16, 0, ($txt));
  $img->trimImage(0);
  $img->quantizeImage(2, Imagick::COLORSPACE_GRAY, 0, false, false );
  $img->setImageDepth(8);
  //$img->setImageColormapColor(1, new ImagickPixel("#000000"));
  //$img->setImageColormapColor(2, new ImagickPixel("#FFFFFF"));
  $img->setImageFormat('png');
  $img->writeImage($dir.'/'.$name.'.png');
}

function png_gen(){
//   for($i=0;$i<256;$i++) {
//     text_to_png(($i+1));  
//   } 
  text_to_png('required');
}

function mktempdir($dir){
  if(!is_dir(WORKDIR.$dir)){
    if(!mkdir(WORKDIR.$dir)){
      return false;
    }  
  }
  define('TMPDIR', WORKDIR.$dir);
  return true;
}

function set_state($content){
  if($content === false){
    return unlink(TMPDIR.'/'.STATEFILE);
  }
  return file_put_contents(TMPDIR.'/'.STATEFILE, $content);
}

function get_state(){
  if (is_file(WORKDIR.$_SESSION['sha1'].'/'.STATEFILE)){
    return file_get_contents(WORKDIR.$_SESSION['sha1'].'/'.STATEFILE);  
  }
  return false;
}

function write_decoded($content, $tmpdir){
  $type = substr($content, 0, strpos($data,"/"));
  $data = substr($content,strpos($content,"/")+1);
  $sha = sha1($data);
  switch($type){
    case 'text':
      $filename = $sha.'txt';
      break;
    case 'file':
      $filename = $sha;
      break;
    default:
      $filename = $type;  
      break;
  }
  try{
    $zip = new ZipArchive;
    $zip->open($tmpdir.'/'.$sha.'.zip', ZipArchive::CREATE);
    //trigger_error($tmpdir.'/'.$sha.'.zip')
    $zip->addFromString($filename, $data);
      //trigger_error($value);
    $zip->close();
  }catch(Exception $e) {
    trigger_error('Imagick caught exception: ' . $e->getMessage());
    return false;     
  }
  $_SESSION['archive'] = $tmpdir.'/'.$sha.'.zip';
  return true;
}


function encode($data, $sha1, $datachunks, $datars, $size, $printnum=false, $printrequired=false, $name=null){
  $checksum = hash('crc32', $data, true);
  if(!mktempdir($sha1)){
    return 'Unable to create temporaty directory';
  }
  if(!is_null($name)){
    $_SESSION['filename'] = $name;
  }else{
    $_SESSION['filename'] = $sha1;
  }
  $_SESSION['pdf'] = TMPDIR.'/'.$sha1;
  session_write_close();
  set_state('Encrypt data');
  usleep(MICROSLEEP);
  //trigger_error($_SESSION['status']);
  $enc = encrypt_data($data, gen_key($datachunks));
  set_state('Split data');
  usleep(MICROSLEEP);
  //trigger_error($_SESSION['status']);
  $data = split_data($enc['data'], $datachunks, $datars);
  $key = split_data($enc['key'], $datachunks, $datars);
  set_state('Compute Galois fields polynomial table');
  usleep(MICROSLEEP);
  //trigger_error($_SESSION['status']);
  $table = set_table();
  set_state('Compute Reed Solomon');
  usleep(MICROSLEEP);
  //trigger_error($_SESSION['status']);
  $reed_solomon_enc = 'reed_solomon_enc_'.base();
  usleep(MICROSLEEP);
  if($datars > 0){
    $rs = $reed_solomon_enc($data, $datars);
    $rskey = $reed_solomon_enc($key, $datars);  
  }
  usleep(MICROSLEEP);
  $lenght_crypt = encrypt_data(pack('L', $data['length']), $enc['key']);
  set_state('Pack into binary');
  usleep(MICROSLEEP);
  //trigger_error($_SESSION['status']);
  foreach($data['data'] as $key1 => $value){
    $qrcode[$key1] = format_enc(base(), 'data', $data['chunks'], $key1, $value, $lenght_crypt['data'], $checksum, $key['data'][$key1]);  
  }
  usleep(MICROSLEEP);
  if($datars > 0){
    foreach($rs as $key2 => $value) {
      $qrcode[$key1+$key2+1] = format_enc(base(), 'rs', $data['chunks'], $key2, $value, $lenght_crypt['data'], $checksum, $rskey[$key2]);
    } 
  }
  //unset variables to free memories
  unset($data);unset($enc);unset($key);unset($rs);unset($checksum);unset($table);unset($reed_solomon_enc);unset($rskey);unset($lenght_crypt);
  set_state('Generate Qrcodes');
  //trigger_error($_SESSION['status']);
  foreach($qrcode as $key => $value) {
  usleep(MICROSLEEP);
    $qr_image[$key] = qrencode($value);  
    if($qr_image[$key] === false){
      set_state(false);
      return 'Qrcodes generation fail';  
    }
  }
  unset($qrcode);
//   set_state('Custom Qrcodes';
//   trigger_error($_SESSION['status']);
//   $qr_image = custom_qrcodes($qr_image, $datachunks, $sha1, $printnum, $printrequired, $name);
//   if($qr_image === false){
//     return 'Custom Qrcodes error';  
//   }
//   set_state('Optimizing PNG';
//   trigger_error($_SESSION['status']);
//   $qr_image = optimize_png($qr_image, $sha1);
  set_state('Create PDF');
  usleep(MICROSLEEP);
  //trigger_error($_SESSION['status']);
  if(pdf_create($qr_image, $sha1, $datachunks, $size, $printnum, $printrequired, $name) === false){
    set_state(false);
    return 'PDF creation fail';
  }
  //unset($_SESSION['status']);
  set_state(false);
  return true;
}


function decode($images, $tmpdir){
  unset($_SESSION['decode_img']);
  session_write_close();
  base(8);
  if(!mktempdir($tmpdir)){
    return 'Unable to create temporaty directory';
  }
  $qrdecode=array();
  set_state('Read images');
  trigger_error('Read images');
  foreach($images as $value){
    $return = qrdecode($value);
    if($return !== false){
      $qrdecode = array_merge($qrdecode, $return);
    }  
  }
  if(count($qrdecode) === 0){
    return 'No qrcodes has been found... be sure they are legible on the pictures';  
  }
  
  set_state('Decode binaries');
  trigger_error('Decode binaries');  
  usleep(MICROSLEEP);
  foreach($qrdecode as $key => $value) {
    $decoded[$key] = format_dec($value);  
  }
  unset($qrdecode);
  set_state('Decode Reed Solomon');
  trigger_error('Decode Reed Solomon');
  $message = retreive_data($decoded);
  unset($decoded);
  if($message === false){
    return 'Decode data fail... be sure you gave enough qrcodes';
  }
  set_state('Create archive');
  trigger_error('Create archive');
  usleep(MICROSLEEP);
  $return = write_decoded($message, $tmpdir);
  unset($message);
  if(!$return){
    set_state(false);
    return 'Writing file fail';
  }
  set_state(false);
  return true;
}
//var_dump(4294967296 * 2913);
// $chunks = 200;
// $rschunks = 10;
// $str ="John Napier a développé les logarithmes au début du xviie siècle. Pendant trois siècles, les tables de logarithmes et les règles à calculs ont été utilisées pour réaliser des calculs, jusqu'à leur remplacement, à la fin du xxe siècle, par des calculatrices. Pour les calculs, le logarithme décimal (c'est-à-dire en base dix) était le plus communément utilisé. Le logarithme népérien (ou naturel) est celui qui utilise le nombre e comme base, il est fondamental en analyse mathématique car il est la fonction réciproque de la fonction exponentielle. Le logarithme binaire, qui utilise 2 comme base, est utile pour les calculs appliqués, et en informatique théorique.";
// $checksum = hash('crc32', $str, true);
// $enc = encrypt_data($str, gen_key($chunks));
// var_dump(strlen($str));


//$dec = decrypt_data($enc['key'], $enc['data']);

//var_dump(strlen($str));
//var_dump(strlen($enc['data']));

// //print_r($enc);
//print_r($dec);

//print_r($str);

// $data = split_data($enc['data'], $chunks, $rschunks);

// $key = split_data($enc['key'], $chunks, $rschunks);

//var_dump($data);
//var_dump($key);
//var_dump(base());

// $table = set_table();
// $reed_solomon_enc = 'reed_solomon_enc_'.base();
//  $rs = $reed_solomon_enc($data, $rschunks);
 //var_dump($rs);
// $rskey = $reed_solomon_enc($key, $rschunks);
// 
//  $lenght_crypt = encrypt_data(pack('L', $data['length']), $enc['key']);
// 
// foreach($data['data'] as $key1 => $value){
//   $qrcode[$key1] = format_enc(base(), 'data', $data['chunks'], $key1, $value, $lenght_crypt['data'], $checksum, $key['data'][$key1]);  
// }
// foreach($rs as $key2 => $value) {
//   $qrcode[$key1+$key2+1] = format_enc(base(), 'rs', $data['chunks'], $key2, $value, $lenght_crypt['data'], $checksum, $rskey[$key2]);
// }

// foreach($qrcode as $key => $value) {
//   $qr_image[$key] = qrencode($value);  
// }

//unset($qr_image[1]);
//unset($qr_image[10]);
//unset($qr_image[11]);
//unset($qr_image[12]);


// $qrdecode=array();
// foreach($qr_image as $value){
//   $qrdecode = array_merge($qrdecode, qrdecode($value));    
// }

// foreach($qrdecode as $key => $value) {
//   $decoded[$key] = format_dec($value);  
// }

//$message = retreive_data($decoded);


//var_dump($message);

//print_r($qrcode);
//unset($qrcode[0]);
//unset($qrcode[15]);

// foreach($qrcode as $key => $value) {
//   $dec_qr[$key] = format_dec($value);  
// }



//print_r($dec_qr);

//var_dump($test);

//var_dump(format_dec($test));

// print_r(retreive_data($dec_qr));






//unset($data['data'][0]);
// unset($data['data'][2]);
// unset($data['data'][6]);
// unset($data['data'][12]);
// unset($data['data'][26]);
// unset($data['data'][68]);
// 
// 
// unset($rs[0]);
// unset($rs[2]);


// $testmatrix=array(
//     array(1,0,0),
//     array(1,1,1),
//     array(1,2,3)
// );
// inverse($testmatrix);

//$recovored = reed_solomon_dec($data['data'], $rs, $data['length'], $data['chunks']);


//  ksort($recovored);
//  $return = '';
//  foreach($recovored as $value) {
//    $return .= $value;  
//  }
 
// print_r(decrypt_data($enc['key'], substr($return, 0, $data['length'])));



//tests
// $test['data'] = array(3, 13, 56);
// $test['chunks'] = 1;
// $rs = reed_solomon_enc($test, 3);
// unset($test['data'][1]);
// unset($test['data'][2]);
// //$rs= array(7, 79, 65);
// $rec = reed_solomon_dec($test['data'], $rs, 3, 1);
// echo('rs : ');
// print_r($rs);
// print_r($rec);


//echo mul(3, 1);

//echo div(1, 7);

//echo expon(6, 2);

?>