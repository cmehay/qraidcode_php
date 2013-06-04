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
  var maxqrcodes = 150;
  var wait_def = 'Please wait...';
  var pdf_icon = 'styles/img/pdf-icon.png';
  

  priv.intform = function(int){
    return int.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); 
  }
  
  priv.setwaitdef = function(){
    $('#wait').html(wait_def);
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
  
  priv.selectactive = function(from){
    //console.log(from);
    var corresp = {
      'file-encode':'text-encode',
      'text-encode':'file-encode'
    };    
    $('#'+corresp[from]).prop("disabled", true);
    $('#'+from).prop("disabled", false);
    $('#'+corresp[from]).parent().css('background-color', '#F3F3F3');
    $('#'+from).parent().css('background-color', 'white');
    $('#'+from).prev().css('pointer-events', 'none');
    $('#'+corresp[from]).prev().css('pointer-events', 'all');
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
    $('#wait').css('opacity', 0);
    priv.setwaitdef();
  };
  
  priv.next1 = function(from, wait){
    priv.setwaitdef();
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
    priv.setwaitdef();
  }
  
  priv.filesize = function(files, maxsize){
    if( typeof files === 'string' ) {
    return false;
    }
    var nb = files.length;
    var size = 0;
    //var reader = new FileReader();
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
  
  priv.checkdecode = function(){
    
  }
  
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
      $('input[name=optiontitle]').prop("disabled", false)
    }
    priv.display_chunks();
    priv.display_rs();
  }
  
  priv.ajax_encode1 = function(data, type, name){
    var sendname = null;
    if(name != 'undefined'){
      sendname = encodeURIComponent(name);
    }
    console.log(data);
    $.ajax({
        url: '?mod=json&action=get_encode_data&type='+type+'&name='+sendname,
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
	console.log('fail là');
	priv.fail(json.msg, 'fourth-step');
	clearInterval(priv.ajaxrefresh)
	return false;
      }
      priv.next2('third-step');
      clearInterval(priv.ajaxrefresh)
    }).fail(function(){
      console.log('fail ici');
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
  
  priv.ajax_decode2 = function(){
    $.ajax({
      url:'?mod=json&action=get_decode',
      type: 'GET',
      dataType: 'json'
    }).done(function(json){
      if(json.error){
	$('#third-step .decode .error').html(json.msg);
	$('#third-step .decode .link').addClass('hidden');
	$('#third-step .decode .error').removeClass('hidden');
      }else{
	$('#third-step .decode .link').removeClass('hidden');
	$('#third-step .decode .error').addClass('hidden');
      }
      priv.next2('second-step');
    }).fail(function(){
      priv.fail('Error occured :( try again', 'third-step');
    });
  }
  
  priv.ajax_decode1 = function(int){
    var total = priv.currents_images.length;
    $('#wait').html('Uploading '+int+'/'+total);
    $.ajax({
      url:'?mod=json&action=send_decode&num='+int,
      type: 'POST',
      data: int+'='+priv.currents_images[int],
      dataType: 'json'
    }).done(function(json){
      if(int < total){
	priv.ajax_decode1(int+1);
      }else{
	priv.ajax_decode2();
      }
    }).fail(function(){
      priv.fail('Error occured :( try again', 'third-step');
    });
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
	priv.ajax_encode1(content['data'], content['type'], priv.filename);
	delete priv.filename;
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
  
  priv.slidedecode1 = function(){
    console.log('loops?');
    if($('.filesize').attr('data-size') > maxlength_decode || $('.filesize').attr('data-size') == 0){
      return false
    }
    priv.next1('second-step', true);
    setInterval(function(){
      priv.currents_images = [];
      $('.thumbnail').each(function(){
	priv.currents_images.push($(this).attr('name'));
      });
      priv.ajax_decode1(0);
    });
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
  
  priv.display_images = function(files){
    //console.log('');
    //var filesize = priv.filesize(files, maxlength_decode);

    for(i=0;i<files.length;i++){
      var file = files[i];
      //console.log(file.type);
      if(!file.type.match('image') && !file.type.match('pdf')){
	//console.log('fail');
        continue;
      }
      var filereader = new FileReader();
      filereader.onload = function(even){
	//console.log('ok');
	priv.add_thumb(even.target.result, this.thissize);
      }
      filereader.readAsDataURL(file);
      filereader.thissize = file.size;
    }    
    setTimeout(function(){
      priv.rangetachambre(false);
      setTimeout(priv.rangetachambre, 400);
    }, 500);
    var filesize = parseInt($('.decode .filesize').attr('data-size'));
    if(filesize > maxlength_decode){
      $('.decode .filesize').html('Too much data (max: '+priv.intform(maxlength_decode)+' bytes)');
      $('#decode-input').addClass('invalid');
      $('#decode-input').prop("disabled", true);
      return false;
    }
    $('.decode .filesize').html(priv.intform(filesize)+' bytes');
    $('.decode .input').css('margin-top', '10px');
    $('#decode-input').prop("disabled", false);
    $('.display-images').css('z-index', 0);

  }
  
  priv.add_thumb = function(image, size){
    var num = 0;
    if(image.match('application/pdf')){
      image = pdf_icon;
    }
    if($('.thumbnail').length > 0){
      num = parseInt($('.thumbnail:last').attr('name'))+1;
    }
    $('<img class="thumbnail" name="'+num+'" src="'+image+'" data-size="'+size+'"/>').appendTo('.display-images');
    $('.thumbnail').click(function(){
      console.log('dadafuk?');
      priv.rm_thumb(this);
    });
  }
  
  priv.rm_thumb = function(that){
    console.log('dafuk?');
    $(that).remove();
    priv.rangetachambre();
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
  
  priv.rangetachambre = function(display){
    var each = [];
    var count = $('.thumbnail').length;
    var seuil = 8;
    if(count < seuil){
      seuil = count;
    }
    var cursize = 0;
    var seuilck = seuil;
    var margin = 3;//px
    var width = parseInt($('.display-images').width());
    var height = parseInt($('.display-images').height());
    var block = {
      'height': Math.floor((height-(margin*2))/Math.ceil(count/seuil)),
      'width': Math.floor((width/seuil)-(margin*2))
    };
    $('.thumbnail').each(function(){
      each.push($(this).attr('name'));
    });
    //console.log(block.height);
    var top = margin;
    var left = margin;
    for(i=0;i<count;i++){
      if(i == seuilck){
	left = margin;
	top = top + block.height;
	seuilck = seuil+seuilck;
      }
      var that = $('.thumbnail[name='+each[i]+']');
      console.log('.thumbnail[name='+each[i]+']');
      console.log(that);
      console.log(that[0].naturalHeight);
      var cur = {
	'height': that[0].naturalHeight,
	'width': that[0].naturalWidth
      }
      console.log(cur);
//       if(cur.height > cur.width){
// 	//console.log(block.width * (cur.height/cur.width));
// 	that.css('height', block.height);
// 	//that.css('width', block.width * (cur.height/cur.width));
// 	that.css('width', '');
//       }else{
// 	//console.log(block.height * (cur.width/cur.height));
// 	that.css('width', block.width);
// 	//that.css('height', block.height * (cur.width/cur.height));
// 	that.css('height', '');
//       }
      that.css('max-height', block.height);
      that.css('max-width', block.width);
      //position
      console.log('heigth '+that.height());
      console.log('width '+that.width()); 
      that.css('top', Math.floor(top+((block.height - that.height())/2)));that.css('left', Math.floor(left+((block.width - that.width())/2)));
      left = left + block.width+(margin*2);
      if(display !== false){
	that.css('opacity', 1);
      }
      cursize = cursize + parseInt(that.attr('data-size'));
    }
    //update filesize
    $('.decode .filesize').html(priv.intform(cursize)+' bytes');
    $('.decode .filesize').attr('data-size', cursize);
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
//     $('#second-step .encode .left').click(function(){
//       machin.selectactive('#text-encode');
//     });
//     $('#second-step .encode .right').click(function(){
//       machin.selectactive('#file-encode');
//     });
    $('#second-step .encode .next').click(function(){
      priv.slideencode1();
    });
    $('#second-step .decode .next').click(function(){
      priv.slidedecode1();
    });
    $('#third-step .encode .next').click(function(){
      priv.slideencode2();
    });
    
    $('#file-encode').change(function(){
      priv.display_filesize(this.files);
    });
    $('#decode-input').change(function(){
      priv.display_images(this.files);
    });
    $('.activate').click(function(){
      priv.selectactive($(this).next().attr('id'));
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