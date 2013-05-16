(function(){
  "use strict";
  var machin = {};
  var priv = {};
  var i;  
  
  machin.firstslide = function(truc){
    $('#first-step').css('right', '700px');
    $('#second-step').css('right', '0px');
    $('#second-step').css('display', 'block');
    $('.'+truc).css('display', 'block');
    
  }
  
  machin.onready = function(){
    $('#encode').click(function(){
      machin.firstslide('encode');
    });
    $('#decode').click(function(){
      machin.firstslide('decode');
    });
  }
  
  window.machin = machin; 
})();


$(document).ready(function(){
  machin.onready();
});