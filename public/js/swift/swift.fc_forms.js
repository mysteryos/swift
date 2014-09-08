/* 
 * Name: Freight Company Forms List
 */
(window.fc_forms = function () {
    /*
    * Fixed table height
    */

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

    $("#inbox-table .inbox-data-type,#inbox-table .inbox-data-from").on('click',function() {
       $.pjax({
          url: $(this).closest('.orderform').attr('data-view'),
          container: '#main'
       });
    });

    $("#inbox-table a.markstar").on('click',function(){
       console.log('hey');
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
                        msg.hide();
                        $this.removeData('ajax');
                    },
                    error:function(xhr, status, error)
                    {
                        return xhr.responseText;
                    }
                });        
       return false;
                           
    });

    //Hide Loading Message
    messenger_hidenotiftop();

})();