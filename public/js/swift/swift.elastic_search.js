/*
 * /Search Js
 */

(window.elastic_search = function () {
    
    $('#search_again').on('submit',function(e){
       $.pjax ({
          container: '#main',
          timeout: 10000,
          url: '/search',
          data: $('#search_again').serialize()
       });
       return false; 
    });  
    
    //Hide Loading Message
    messenger_hidenotiftop();    
})();