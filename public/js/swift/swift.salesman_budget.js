(window.salesman_budget = function() {
    
    $('.budget-chart').each(function(){
        console.log($(this).attr('id'));
        console.log($(this).parent().find('.budget-value').val());
        Morris.Bar({
          element: $(this).closest('.budget-chart').attr('id'),
          data: $.parseJSON($(this).parent().find('.budget-value').val()),
          xkey: 'scheme_name',
          ykeys: ['budget', 'actual'],
          labels: ['Budget', 'Actual Sales']
        });        
    });
    
    //Hide Loading Message
    messenger_hidenotiftop();
})();