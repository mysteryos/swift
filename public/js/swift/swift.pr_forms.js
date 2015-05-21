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
     
    tableHeightSize();

    $(window).resize(function() {
           tableHeightSize()
    })

    function tableHeightSize() {

           var tableHeight = $(window).height() - 212;

           if (tableHeight < 320) {
                   $('.table-wrap').css('height', 320 + 'px');
           } else {
                   $('.table-wrap').css('height', tableHeight + 'px');
           }

    }

    //Gets tooltips activated
    $("#inbox-table [rel=tooltip]").tooltip();

    $("#inbox-table input[type='checkbox']").change(function() {
           $(this).closest('tr').toggleClass("highlight", this.checked);
    });

    $("#inbox-table .inbox-data-message,#inbox-table .inbox-data-from").on('click',function() {
       $.pjax({
          url: $(this).closest('.orderform').attr('data-view'),
          container: '#main'
       });
    });

    $("#inbox-table a.markstar").on('click',function(){
       var $this = $(this);

       if(typeof $this.data('ajax') !== "undefined")
       {
           $this.data('ajax').abort();
           $this.removeData('ajax');
       }

       if($this.children('i').hasClass('fa-star-o'))
       {
           $this.children('i').removeClass('fa-star-o');
           $this.children('i').addClass('fa-star');
       }
       else
       {
           $this.children('i').addClass('fa-star-o');
           $this.children('i').removeClass('fa-star');
       }

        var msg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
            id: 'notif-top',
            progressMessage: 'Working..',
            action: $.ajax,
        },
        {
            type:'PUT',
            url: $this.attr('href'),
            data: {'toggle': $this.hasClass('fa-star')},
            beforeSend: function(){
                $this.data('ajax',$(this));
            },
            success:function()
            {
                $this.removeData('ajax');
                msg.hide();
            },
            error:function(xhr, status, error)
            {
                $this.removeData('ajax');
                return xhr.responseText;
            }
        });   

       return false;
    });     

    //Hide Loading Message
    messenger_hidenotiftop();    
})();