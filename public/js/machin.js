(function(){
  "use strict";
  var machin = {};
  var priv = {};
  var i;  
  
  //config
  var maxlength_encode = 300000;
  var maxlength_decode = 20000000;
  var slide_duration = 1000;
  
  machin.firstslide = function(truc){
    var corresp = {
      'encode':'decode',
      'decode':'encode'
    };
    $('#first-step').css('right', '700px');
    $('.'+truc).css('display', 'block');
    $('.'+corresp[truc]).css('display', 'none');
    $('#second-step').css('right', '0px');
  };
  
  machin.selectactive = function(from){
    var corresp = {
      '.file-encode':'.text-encode',
      '.text-encode':'.file-encode'
    };    
    $(corresp[from]).prop("disabled", true);
    $(from).prop("disabled", false);
    $(corresp[from]).parent().css('background-color', '#F3F3F3');
    $(from).parent().css('background-color', 'white');
  };
  
  machin.prev = function(from){
    var corresp = {
      'second-step':'first-step',
      'third-step':'second-step',
      'fourth-step':'third-step'
    };
    $('#'+from).css('right', '-700px');
    console.log(corresp[from]);
    $('#'+corresp[from]).css('right', '0px');
  };
  
  priv.next1 = function(from, wait){
    var corresp = {
      'second-step':'third-step',
      'third-step':'fourth-step',
    };
    $('#'+from).css('right', '700px');
    if(wait){
      setTimeout(function(){$('.wait').css('opacity', 100);}, slide_duration);
      return true;
    }
    $('#'+corresp[from]).css('right', '0px');
  }
  
  priv.next2 = function(from){
    var corresp = {
      'second-step':'third-step',
      'third-step':'fourth-step',
    };
    $('.wait').css('opacity', 100);
    setTimeout(function(){$('#'+corresp[from]).css('right', '0px');}, 110);
  }
  
  priv.filesize = function(files, maxsize){
    if( typeof files === 'string' ) {
    files = [ files ];
    }
    var nb = files.length;
    var size = 0;
    for(i=0;i<nb;i++){
      size += files[i].size;
    }
    if((size > maxsize) || (size == 0)){
      return false;
    }
    return true;
  };
  
  //http://stackoverflow.com/questions/2848462/count-bytes-in-textarea-using-javascript
  priv.textsizebytes = function(string){
    var utf8length = 0;
    for (var n = 0; n < string.length; n++) {
        var c = string.charCodeAt(n);
        if (c < 128) {
            utf8length++;
        }
        else if((c > 127) && (c < 2048)) {
            utf8length = utf8length+2;
        }
        else {
            utf8length = utf8length+3;
        }
    }
    return utf8length;
 }
  
  priv.checkencode1 = function(){
    if($('.file-encode').is(':disabled')){
      var textarea = $('.text-encode').val();
      if(priv.textsizebytes(textarea) < maxlength_encode){
	return textarea;
      }
    }
    else if($('.text-encode').is(':disabled')){
      var file = $('.file-encode');
      if(priv.filesize(file, maxlength_encode)){
	return file.files[0];
      }
    }
    return false;
  };
  
  priv.fail = function(msg, prev){
    $('.wait').html(msg);
	  setTimeout(function(){
	    machin.prev(prev);
	  }, 500);
  };
  
  priv.ajax_encode1 = function(data){
     $.ajax({
        url: "?mod=json&action=get_encode_data",
        type: "POST",
        data: file,
        processData: false,
	dataType: 'json'
    }).done(function(json){
	if(json.error){
	  priv.fail(json.msg, 'third-step');
	  return false;
	}
	priv.next2('second-step');
      }
    ).fail(function(){
      priv.fail('Error occured :(', 'third-step');
    };
  }
  
  machin.slideencode1 = function(){
    var content = priv.checkencode1;
    if(content == false){
      return false;
    }
    priv.next1('second-step', true);
    setTimeout(function(){
      
    },slide_duration);
  }
  
  machin.onready = function(){
    $('#encode').click(function(){
      machin.firstslide('encode');
    });
    $('#decode').click(function(){
      machin.firstslide('decode');
    });
    $('.prev').click(function(){
      machin.prev($(this).parent().parent().attr('id'));
    });
    $('#second-step .encode .left').click(function(){
      machin.selectactive('.text-encode');
    });
    $('#second-step .encode .right').click(function(){
      machin.selectactive('.file-encode');
    });
  };
  
  window.machin = machin; 
})();


$(document).ready(function(){
  machin.onready();
});