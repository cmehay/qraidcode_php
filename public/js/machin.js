(function(){
  "use strict";
  var machin = {};
  var priv = {};
  var i;  
  
  machin.firstslide = function(truc){
    var corresp = {
      'encode':'decode',
      'decode':'encode'
    };
    
    $('#first-step').css('right', '700px');
    //$('#second-step').css('display', 'block');
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
  
  machin.next = function(from){
    
    
  };
  
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
    $('.text-encode').click(function(){
      machin.selectactive('.text-encode');
    });
    $('.file-encode').click(function(){
      machin.selectactive('.file-encode');
    });
  };
  
  window.machin = machin; 
})();


$(document).ready(function(){
  machin.onready();
});