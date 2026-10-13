/* 
  -------
  
    SCRIPT.JS
  
  -------
*/
jQuery(document).ready(function(){
  console.log('domready');
  jQuery.each([52,97], function(index, value){
    console.log('domready '+ index + ":" + value);
  });
});