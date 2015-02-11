/* 
 * Name: Order Tracking Forms View
 * Description:
 */

/*
* Fixed table height
*/
(window.salesman_lists = function(){
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
          url: $(this).closest('.salesmanform').attr('data-view'),
          container: '#main'
       });
    });

    //Hide Loading Message
    messenger_hidenotiftop();
})();