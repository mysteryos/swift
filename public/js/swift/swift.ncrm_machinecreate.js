/* 
 * Name: Nespresso CRM Create Machine form
 * Description: 
 */
(window.ncrm_machinecreate = function() {
    $('#selectmachine').select2({
        placeholder: 'Enter a machine name',
        allowClear: true,
        minimumInputLength: 0,
        positionDropdownAbsolute: false,
        ajax: {
             url: "/ajaxsearch/nespressomachine",
             dataType: "json",
             quietMillis: 500,
             data: function (term, page) {
                 return {
                     term: term,
                     limit: 10,
                     page: page
                 };
             },
             results: function (data, page){
                 var more = (page * 15) < data.total
                 if(data.total > 0)
                 {
                     var found;
                     found = $.map(data.machines, function (item) {
                         item.LITM = $.trim(item.LITM);
                         return {
                             id: item.LITM,
                             name: item.DSC1,
                             text: item.DSC1+" (Code:"+item.LITM+")",
                             category: item.SRP3
                         }
                      });
                     return {results: found, more:more};
                 }
                 else
                 {
                     return {results: ''};
                 }
             },
        },            
    });

    $('#selectcustomer').select2({
        placeholder: 'Enter a customer code/name',
        allowClear: true,
        minimumInputLength: 2,
        positionDropdownAbsolute: false,
        ajax: {
             url: "/ajaxsearch/customercode",
             dataType: "json",
             quietMillis: 500,
             data: function (term, page) {
                 return {
                     term: term,
                     limit: 10,
                     page: page
                 };
             },
             results: function (data, page){
                 var more = (page * 15) < data.total
                 if(data.total > 0)
                 {
                     var found;
                     found = $.map(data.customers, function (item) {
                         return {
                             id: item.AN8,
                             name: item.ALPH,
                             text: item.ALPH+" (Code:"+item.AN8+")",
                             category: item.AC09
                         }
                      });
                     return {results: found, more:more};
                 }
                 else
                 {
                     return {results: ''};
                 }
             },
        },            
    });

    //Hide Loading Message
    messenger_hidenotiftop();
})();