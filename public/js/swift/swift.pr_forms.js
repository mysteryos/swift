(window.pr_forms = function(){

    $('#filter-btn').popover({
        content: function() {
            return document.getElementById('filter-popover').innerHTML;
        },
        html: true,
        placement: 'bottom',
        trigger: 'manual',
        container: '#filter-btn'
     }).on("shown.bs.popover",function(){
         var $filterBtnDatePicker = $('#filter-btn').find('.datepicker');
         $filterBtnDatePicker.datepicker('destroy');        
         $filterBtnDatePicker.removeClass('hasDatePicker');
         $filterBtnDatePicker.datepicker({
             dateFormat : 'dd/mm/yy',
             prevText : '<i class="fa fa-chevron-left"></i>',
             nextText : '<i class="fa fa-chevron-right"></i>',            
         });

         $('#filter-btn').find('#filter-btn-close').on("click",function(){
             $('#filter-btn').popover('hide');
             return false;
         });

         $('#filter-btn').find('form').on('submit',function(e){
              $.pjax.submit(e,'#main');
              return false;
         });

     }).on("hide.bs.popover",function(){
         $('#filter-btn').find('.datepicker').datepicker('destroy');
     });

     $('#filter-btn.popover-trigger').on("click",function(e){
         if(!$(e.target).parents('div.popover').length)
         {
             if($(this).find('div.popover:visible').length)
             {
                 $(this).popover('hide');
             }
             else
             {
                 $(this).popover('show');
             }
         }
     });    

    //Hide Loading Message
    messenger_hidenotiftop();    
})();