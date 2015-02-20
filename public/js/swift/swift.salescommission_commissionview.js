(window.salescommission_commissionview = function() {
    
    $('a.get-detailed').on('click',function(e){
        e.preventDefault();
        var $this = $(this);
        $this.addClass('loading');
        $this.parents('.widget-body').load($this.prop('href'),function(){
            $this.removeClass('loading');
        });
        return false;
    });
    
    messenger_hidenotiftop();
})();