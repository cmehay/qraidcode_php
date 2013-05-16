(function(){
  "use strict";
  var machin = {};
  var priv = {};
  var i;  
  
  machin.firstslide = function(truc){
    $('#first-step').css('right', '700px');
    //$('#second-step').css('display', 'block');
    $('.'+truc).css('display', 'block');
    $('#second-step').css('right', '0px');
  };
  
  machin.prev = function(from){
    var corresp = {
      'second-step':'first-step',
      'third-step':'second-step',
      'fourth-step':'third-step'
    };
    $('#'+from).css('right', '-700px');
    $('#'+corresp.from).css('right', '0px');
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
  };
  
  window.machin = machin; 
})();


$(document).ready(function(){
  machin.onready();
});