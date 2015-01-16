/**
 * Name: APRequest- Statistics
 */

(window.apr_statistics = function() {
    
    var productPieChart,customerPieChart,requesterPieChart;
    
    //Product Pie chart
    $.ajax({
       url:'/aprequest/chart',
       dataType : 'json',
       type:'POST',
       data: $('#productPieChartForm').serialize(),
       success:function(dataJson)
       {
            productPieChart  =  Morris.Donut({
                    element: 'productPieChart',
                    formatter: function(y, data){
                        return "Rs " + y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    },
                    data: dataJson
            }); 
       }
    });
    
    // Customer Pie Chart
    
    $.ajax({
       url:'/aprequest/chart',
       dataType : 'json',
       type:'POST',
       data: $('#customerPieChartForm').serialize(),
       success:function(dataJson)
       {
            customerPieChart = Morris.Donut({
                element: 'customerPieChart',
                formatter: function(y, data){
                    return "Rs " + y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                },                
                data: dataJson,
            });          
       }
    });   
    
    // Requester Pie Chart
    $.ajax({
        url:'/aprequest/chart',
        dataType : 'json',
        type:'POST',
        data: $('#requesterPieChartForm').serialize(),
        success:function(dataJson)
        {
             requesterPieChart = Morris.Donut({
                 element: 'requesterPieChart',
                 formatter: function(y, data){
                     return "Rs " + y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                 },                
                 data: dataJson,
             });          
        }        
    });
    
        $("input.datepicker").datepicker({
            dateFormat : "yy/mm/dd",
            prevText : '<i class="fa fa-chevron-left"></i>',
            nextText : '<i class="fa fa-chevron-right"></i>',
        });    
    
    $('#productPieChartForm,#customerPieChartForm,#requesterPieChartForm').on('submit',function(e){
        e.preventDefault();        
        var $this = $(this);
        $this.find('button[type="submit"]').attr('disabled','disabled').html('Loading..');

        $.ajax({
           url:'/aprequest/chart',
           dataType : 'json',
           type:'POST',
           data: $this.serialize(),
           success:function(dataJson)
           {
                $this.find('button[type="submit"]').removeAttr('disabled').html('Submit');
                $this.parents('div.widget-body').find('div.chart').html('');
                Morris.Donut({
                    element: $this.parents('div.widget-body').find('div.chart').attr('id'),
                    formatter: function(y, data){
                        return "Rs " + y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    },                
                    data: dataJson,
                });          
           },
           error:function(xhr, ajaxOptions, thrownError)
           {
               $this.find('button[type="submit"]').removeAttr('disabled').html('Submit');
               messenger_notiftop(xhr.responseText,"error");
           }
        });        
    });
    
    //Hide Loading Message
    messenger_hidenotiftop();
})();