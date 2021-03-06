<?php
set_time_limit(400);
session_start();

//index
require '../conf.php';
require '../html.php';
require '../json.php';
require '../qraidcode.php';
require '../functions.php';
require TFPDF;

delete_old();

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
	  <div class="activate"></div>
	  <textarea id="text-encode" class="invalid"></textarea>
	  <div class="textsize">'.number_format(MAXINPUT).' bytes remaining</div>
	</div>
	<div class="right input">
	  <div class="description">or your file below</div>
	  <form id="form-encode" enctype="multipart/form-data">
	    <div class="activate"></div>
	    <input id="file-encode" class="invalid" type="file" id="inputsimple" name="inputsimple" /><div class="description2 filesize">('.number_format(MAXINPUT).' bytes max)</div>
	  </form>
	</div>
	<div class="prev">'.htmlentities('<-- prev').'</div>
	<div class="next">'.htmlentities('next -->').'</div>
      </div>
      <div class="decode">
	<div class="description">Select pictures of your qrcodes</div><div class="description">(they can all be in the same picture as long they are full and legible)</div>
	<div class="input"><input id="decode-input" class="invalide" type="file" id="inputmultiple" name="inputmultiple" multiple /></div>
	<div class="description filesize" data-size="0">(Use CTRL key to select severall files if needed)</div>
	<div class="display-images"></div>
	<div class="prev">'.htmlentities('<-- prev').'</div>
	<div class="next">'.htmlentities('next -->').'</div>
      </div>
    </div>
    <div id="third-step">
      <div class="encode">
	<div class="description">Set how many qrcodes you want</div>
	<div class="option"><div class="first">Data:</div><div class="wrapper"><input class="range chunks" name="chunks" type="range" value="0" max="0" min="0" step="1" /></div><div class="second chunks"></div></div>
	<div class="option"><div class="first">Parity:</div><div class="wrapper"><input class="range rs" type="range" name="rs" value="0" max="0" min="0" step="1" /></div><div class="second rs"></div></div>
	<div class="description">You will get <span id="datapartotal"></span> qrcodes and you will need at least <span id="datatotal"></span> qrcodes to decode your data</div>
	<div class="description newblock">Options:</div>
	<div class="option2"><label><input type="checkbox" name="checkbox[count]" /> Print numerotation</label></div>
	<div class="option2"><label><input type="checkbox" name="checkbox[total]" /> Print how many qrcodes are required to decode the data</label></div>
	<div class="option2"><label><input class="desc" type="checkbox" name="checkbox[desc]" /> Print a description or a title</label><input type="text" name="optiontitle" maxlength="50" disabled /></div>
	<div class="description newblock">Which size for your qrcodes?</div>
	<div class="option"><div class="first">Size:</div><div class="wrapper"><input class="range size" type="range" name="size" value="6" max="'.(MAXSIZE/10).'" min="'.(MINSIZE/10).'" step="0.5" /></div><div class="second size"></div></div>
	<div class="prev">'.htmlentities('<-- prev').'</div>
	<div class="next">'.htmlentities('next -->').'</div>
      </div>
      <div class="decode">
	<div class="link"><a href="?mod=file&action=getarchive">Download your data</a></div>
	<div class="error millieu hidden"></div>
	<div class="prev">'.htmlentities('<-- prev').'</div>
      </div>
    </div>
    <div id="fourth-step">
      <div class="encode">
	<div class="link"><a href="?mod=file&action=getpdf">Download your qrcodes</a></div>
	<div class="prev">'.htmlentities('<-- prev').'</div>
      </div>
    </div>
  </div>
  <div id="subtitle">1<span class="bold">Go1dy</span>1GRBAHbPUTu6xPaWPSqQPd4DzU2i</div>

  <div id="footer">
    <div class="button">About</div>
    <div class="bellow"><div class="inner">'.html_about().'</div></div>
  </div>
  '
  );
}

echo(parse());
