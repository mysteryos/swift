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
    })
    
    
    //Hide Loading Message
    messenger_hidenotiftop();
})();