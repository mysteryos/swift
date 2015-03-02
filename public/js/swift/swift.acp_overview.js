/**
 * Name: ACPRequest- Overview
 */

(window.acp_overview = function() {
    
    $(".js-status-update a").on('click',function() {
            var selText = $(this).text();
            var $this = $(this);
            $this.parents('.btn-group').find('.dropdown-toggle').html(selText + ' <span class="caret"></span>');
            $this.parents('.dropdown-menu').find('li').removeClass('active');
    });
    
    //Hide Loading Message
    messenger_hidenotiftop();      
})();