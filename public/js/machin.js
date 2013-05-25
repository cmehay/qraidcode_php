(function(){
  "use strict";
  var machin = {};
  var priv = {};
  var i;  
  priv.int = false;
  //config
  var maxlength_encode = 300000;
  var maxlength_decode = 20000000;
  var slide_duration = 1000;
  var maxqrcodes = 256;
  

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
      setTimeout(function(){$('#wait').css('opacity', 1);}, slide_duration/2);
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
    priv.filename = files[0].name;
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
 
  priv.readsendfile = function(id){
    //console.log('et là');
    var file = document.getElementById(id).files[0];
    var filereader = new FileReader();
    filereader.onload = function (event) {
      //console.log('là');
      priv.ajax_encode1('data='+event.target.result, 'file');
    };
    setTimeout(function(){
      console.log('avantdernier');
      $('#'+id).filter(function(){  
	filereader.readAsDataURL(this.files[0]);
	//$(this).dequeue();
	console.log('dernier');
      });
    },slide_duration);
  }
  
  priv.checkencode1 = function(){
    if($('#file-encode').is(':disabled') && !$('#text-encode').is('.invalid')){
	return {'data':'data='+encodeURIComponent($('#text-encode').val()), 'type':'text'};
    }
    else if($('#text-encode').is(':disabled') && !$('#file-encode').is('.invalid')){
      priv.readsendfile('file-encode');
//       var file = document.getElementById('file-encode').files[0];
//  	var filereader = new FileReader();
// 	filereader.onload = function (event) {
// 	  priv.ajax_encode1('data='+event.target.result, 'file');
// 	};
//  	$('#file-encode').queue(function(){
//  	  
//  	  filereader.readAsDataURL(this.files[0]);
//  	});
 	return {'data':'file-encode', 'type':'file'};
    }
    return false;
  };
  
  priv.fail = function(msg, prev){
    $('#wait').html(msg);
    setTimeout(function(){
      machin.prev(prev);
    }, 500);
  };
  
  priv.setencodevalue = function(json){
    $('.range.chunks').attr('min', json.minqr);
    $('.range.chunks').attr('max', json.maxqr);
    $('.range.rs').attr('min', json.minrs);
    $('.range.rs').attr('max', json.maxrs);
    if(typeof priv.filename != 'undefined'){
      $('input.desc').prop("checked", true);
      $('input[name=optiontitle]').val(priv.filename);
    }
    priv.display_chunks();
    priv.display_rs();
  }
  
  priv.ajax_encode1 = function(data, type){   
    console.log(data);
    $.ajax({
        url: '?mod=json&action=get_encode_data&type='+type,
        type: 'POST',
        data: data,
        processData: false,
	//cache: false,
        //contentType: false,
	dataType: 'json'
    }).done(function(json){
	console.log(json);
	//console.log(json.error);
	if(json.error == true){
	  priv.fail(json.msg, 'third-step');
	  return false;
	}
	priv.setencodevalue(json);
	priv.next2('second-step');
      }
    ).fail(function(){
      priv.fail('Error occured :( try again', 'third-step');
    });
  }
  
  priv.ajax_encode2 = function(data){
    $.ajax({
      url:'?mod=json&action=get_encode_option',
      type: 'POST',
      data: data,
      dataType: 'json'
    }).done(function(json){
      clearInterval(priv.ajaxrefresh);
      if(json.error == true){
	priv.fail(json.msg, 'fourth-step');
	return false;
      }
      priv.next2('third-step');
    }).fail(function(){
      clearInterval(priv.ajaxrefresh);
      priv.fail('Error occured :( try again', 'fourth-step');
    });
  }
  
  priv.getstatus = function(action){
    $.ajax({
      url:'?mod=json&action=get_'+action+'_status',
      type: 'GET',
      dataType: 'json'
    }).done(function(json){
      $('#wait').html(json.msg);
    })
  }
  
  priv.slideencode1 = function(){
    var content = priv.checkencode1();
    console.log(content);
    if(content == false){
      return false;
    }
    priv.next1('second-step', true);
    setTimeout(function(){
      if(content['type'] == 'file'){
	//console.log('ici');
	priv.readsendfile(content['data']);
      }
      else{
	delete priv.filename;
	priv.ajax_encode1(content['data'], content['type']);
      }
    },slide_duration);
  }
  
  priv.slideencode2 = function(){
    priv.next1('third-step', true);
    setTimeout(function(){
      priv.ajax_encode2($('#third-step .encode :input').serialize());
      setTimeout(function(){
	priv.ajaxrefresh=setInterval(function(){priv.getstatus('encode')},1000);
      },1000);
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
  
  priv.display_chunks = function(){
    var data=$('.range.chunks').val();
    $('.second.chunks').html(data);
    //update rs
    $('.range.rs').attr('max', maxqrcodes - parseInt(data));
    priv.display_rs();
    var rs=$('.range.rs').val();
    $('.range.rs').val(parseInt(rs)-1);
    $('.range.rs').val(rs);
    $('#datapartotal').html(parseInt(data) + parseInt(rs));
    $('#datatotal').html(data);
  }
  
  priv.display_rs = function(){
    var data=$('.range.chunks').val();
    var rs=$('.range.rs').val();
    $('.second.rs').html(rs);
    $('#datapartotal').html(parseInt(data)+parseInt(rs));
    $('#datatotal').html(data);   
  }
  
  priv.display_size = function(){
    var cur=$('.range.size').val();
    $('.second.size').html(cur+' cm');
  }
  
  priv.switchclick = function(to, active){
    $(to).prop("disabled", active);
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
      priv.slideencode1();
    });
    $('#third-step .encode .next').click(function(){
      priv.slideencode2();
    });
    $('#file-encode').change(function(){
      priv.display_filesize(this.files);
    });
    $('#text-encode').bind("keyup change", function() {
      priv.display_textlength($(this).val());
    });
    priv.display_size();
    $('.range.size').change(priv.display_size);
    $('.range.chunks').change(priv.display_chunks);
    $('.range.rs').change(priv.display_rs);
    $('input.desc').change(function(){
      priv.switchclick('input[name=optiontitle]', !$(this).is(':checked'));
    });
  };
  
  window.machin = machin; 
})();


$(document).ready(function(){
  machin.onready();
});