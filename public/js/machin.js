(function(){
  "use strict";
  var machin = {};
  var priv = {};
  var i;  
  
  //config
  var maxlength_encode = 300000;
  var maxlength_decode = 20000000;
  var slide_duration = 1000;
  

  priv.intform = function(int){
    return int.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); 
  }
  
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
      '#file-encode':'#text-encode',
      '#text-encode':'#file-encode'
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
    $('#wait').css('opacity', 0)
  };
  
  priv.next1 = function(from, wait){
    var corresp = {
      'second-step':'third-step',
      'third-step':'fourth-step',
    };
    $('#'+from).css('right', '700px');
    if(wait){
      setTimeout(function(){$('#wait').css('opacity', 1);}, slide_duration);
      return true;
    }
    $('#'+corresp[from]).css('right', '0px');
  }
  
  priv.next2 = function(from){
    var corresp = {
      'second-step':'third-step',
      'third-step':'fourth-step',
    };
    $('#wait').css('opacity', 0);
    setTimeout(function(){$('#'+corresp[from]).css('right', '0px');}, 110);
  }
  
  priv.filesize = function(files, maxsize){
    if( typeof files === 'string' ) {
    return false;
    }
    var nb = files.length;
    var size = 0;
    var reader = new FileReader();
    var filescontent = [];
    for(i=0;i<nb;i++){
      size += files[i].size;
      //console.log(files[i]);
      //filescontent.push(reader.readAsDataURL(files[i]));
      //filescontent.push('caca');
    }
    //console.log(nb);
    //console.log(size);
    if((size > maxsize) || (size == 0)){
      return false;
    }
    //priv.filescontent = filescontent;
    //console.log(priv.filescontent);
    return size;
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
    if($('#file-encode').is(':disabled') && !$('#text-encode').is('.invalid')){
	return {'data':'data='+encodeURIComponent($('#text-encode').val()), 'type':'text'};
    }
    else if($('#text-encode').is(':disabled') && !$('#file-encode').is('.invalid')){
      var file = new FormData($('#file-encode'));
      //var file = document.getElementById('file-encode').files[0];
// 	var filereader = new FileReader();
// 	$('#file-encode').queue(function(){
// 	  
// 	  priv.filemachin = {'data':filereader.readAsDataURL(this.files[0]), 'type':'file'};
// 	});
	return {'data':file, 'type':'file'};
    }
    return false;
  };
  
  priv.fail = function(msg, prev){
    $('#wait').html(msg);
	  setTimeout(function(){
	    machin.prev(prev);
	  }, 500);
  };
  
  priv.ajax_encode1 = function(data, type){
    console.log(data);
     $.ajax({
        url: '?mod=json&action=get_encode_data&type='+type,
        type: 'POST',
        data: data,
        processData: false,
	cache: false,
        contentType: false,
	dataType: 'json'
    }).done(function(json){
	console.log(json);
	//console.log(json.error);
	if(json.error == true){
	  priv.fail(json.msg, 'third-step');
	  return false;
	}
	priv.next2('second-step');
      }
    ).fail(function(){
      priv.fail('Error occured :(', 'third-step');
    });
  }
  
  machin.slideencode1 = function(){
    var content = priv.checkencode1();
    console.log(content);
    if(content == false){
      return false;
    }
    priv.next1('second-step', true);
    setTimeout(function(){
      priv.ajax_encode1(content['data'], content['type']);
    },slide_duration);
  }
  
  priv.display_filesize = function(file){
    var filesize = priv.filesize(file, maxlength_encode);
    if(filesize == false){
      $('.filesize').html('File too large or empty');
      $('#file-encode').addClass('invalid');
      return false;
    }
    $('.filesize').html(priv.intform(filesize)+' bytes');
    $('#file-encode').removeClass('invalid');
  }
  
  priv.display_textlength = function(text){
    var textsize = priv.textsizebytes(text);
    if(textsize > maxlength_encode){
      $('.textsize').html('Too many words!!!');
      $('#text-encode').addClass('invalid');
      return false;
    }
    if(textsize === 0){
      $('.textsize').html(priv.intform((maxlength_encode - textsize))+' bytes remaining');
      $('#text-encode').addClass('invalid');
      return false;
    }
    $('.textsize').html(priv.intform((maxlength_encode - textsize))+' bytes remaining');
    $('#text-encode').removeClass('invalid');
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
      machin.selectactive('#text-encode');
    });
    $('#second-step .encode .right').click(function(){
      machin.selectactive('#file-encode');
    });
    $('#second-step .encode .next').click(function(){
      machin.slideencode1();
    });
    $('#file-encode').change(function(){
      priv.display_filesize(this.files);
    });
    $('#text-encode').bind("keyup change", function() {
      priv.display_textlength($(this).val());
    });
  };
  
  window.machin = machin; 
})();


$(document).ready(function(){
  machin.onready();
});