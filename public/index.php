<?php
//index
require '../conf.php';
require '../html.php';
require '../json.php';
require '../qraidcode.php';
require '../functions.php';



trigger_error(print_r($_POST, true));

function parse() {

  if(isset($_GET['action'])){
    if (isset($_GET['mod'])){
      $mod=$_GET['mod'];
    }else{$mod='html';}
    if(!is_array($mod) && !is_array($_GET['action'])){
      $callback=$mod.'_cb_'.$_GET['action'];
      if(function_exists($callback)){
	    return $callback();
      }else(trigger_error($callback.' is not callable'));
    }
  }
  
  return index_page();

}

function index_page(){
  return render(
 '<div id="title">QRaidCODE</div>
  <div id="subtitle">A nice qrcode hack to secure your important data over the years</div>
  <div id="zone">
    <div id="wait">Please wait...</div>
    <div id="first-step">
	<div class="left button" id="encode">Encode</div>
	<div class="right button" id="decode">Decode</div>      
    </div>
    <div id="second-step">
      <div class="encode">
	<div class="left input">
	  <div class="description">First enter your message</div>
	  <textarea id="text-encode" class="invalid"></textarea>
	  <div class="textsize">'.number_format(MAXINPUT).' bytes remaining</div>
	</div>
	<div class="right input">
	  <div class="description">or your file below</div>
	  <form id="form-encode" enctype="multipart/form-data">
	    <input id="file-encode" class="invalid" type="file" id="inputsimple" name="inputsimple" /><div class="description2 filesize">('.number_format(MAXINPUT).' bytes max)</div>
	  </form>
	</div>
	<div class="prev">'.htmlentities('<-- prev').'</div>
	<div class="next">'.htmlentities('next -->').'</div>
      </div>
      <div class="decode">
	<div class="description">Select pictures of your qrcodes</div><div class="description">(they can all be in the same picture as long they are full and legible)</div>
	<div class="input"><input type="file" id="inputmultiple" name="inputmultiple" multiple /></div>
	<div class="description">(Use CTRL key to select severall files if needed)</div>
	<div class="prev">'.htmlentities('<-- prev').'</div>
	<div class="next">'.htmlentities('next -->').'</div>
      </div>
    </div>
    <div id="third-step">
      <div class="encode">
	<div class="description">Set how many qrcodes you want</div>
	<div class="option"><div class="first">Data:</div><input class="range chunks" name="chunks" type="range" value="0" max="0" min="0" step="1" /><div class="second"></div></div>
	<div class="option"><div class="first">Parity:</div><input class="range rs" type="range" name="rs" value="0" max="0" min="0" step="1" /><div class="second"></div></div>
	<div class="description">You will get <span id="datapartotal"></span> qrcodes and you will need at less <span id="datatotal"></span> qrcodes to decode your data</div>
	<div class="description">Options:</div>
	<div class="option"><label><input type="checkbox" name="option" value="count" /> Print the qrcode numerotation</label></div>
	<div class="option"><label><input type="checkbox" name="option" value="total" /> Print how many qrcodes are required to decode the data</label></div>
	<div class="option"><label><input type="checkbox" name="option" value="desc" /> Print a description or a title</label></div>
	<div class="option retrait"><input type="text" name="optiontitle" /></div>	
	<div class="description">Which size for your qrcodes?</div>
	<div class="option"><div class="first">Size:</div><input class="range size" type="range" name="size" value="6" max="20" min="4" step="0.5" /><div class="second"></div></div>
	<div class="prev">'.htmlentities('<-- prev').'</div>
	<div class="next">'.htmlentities('next -->').'</div>
      </div>
      <div class="decode">
	<div class="display_msg"></div>
	<div class="prev">'.htmlentities('<-- prev').'</div>
      </div>
    </div>
    <div id="fourth-step">
      <div class="encode"> 
	<div class="display_msg"></div>
	<div class="prev">'.htmlentities('<-- prev').'</div>	
      </div>
    </div>  
  </div>        
  <div id="subtitle">IMPORTANT: this application is for demonstration purposes only. Do not use it for important or critical data.</div>
  <div id="footer">
    <div><a href="/?action=about">About</div>
  </div>
  '
  );
}


echo(parse());


?>