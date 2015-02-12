(window.salescommission_viewscheme = function () {
    //General Info
    $('.editable:not(.dummy)').editable({
        disabled: true
    });
    
    //Enable Commenting
    enableComments();
    
    //Bind pusher channel
    pusherSubscribeCurrentPresenceChannel(true,true);

    //Hide Loading Message
    messenger_hidenotiftop(); 

})();