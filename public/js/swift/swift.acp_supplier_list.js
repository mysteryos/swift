(window.acp_supplier_list = function(){
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
    
    $("#inbox-table .supplierform td").on('click',function() {
       $.pjax({
          url: $(this).closest('.supplierform').attr('data-view'),
          container: '#main'
       });
    });

    //Hide Loading Message
    messenger_hidenotiftop();
})();