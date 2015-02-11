/* 
 * Name: Order Tracking View Form
 */
(window.salesman_view = function () {
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