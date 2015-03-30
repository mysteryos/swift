/* 
 * Name: Order Tracking View Form
 */
(window.ot_view = function () {
    //General Info
    $('.editable:not(.dummy)').editable({
        disabled: true
    });

    //File View
    $('a.file-view').on('click',function(e){
        e.preventDefault();
        var $this = $(this);
        $.colorbox({
           href: "http://docs.google.com/viewer?url="+$this.attr('href')+"&embedded=true",
           maxHeight:"100%",
           maxWidth:"90%",
           innerWidth:"100%",
           innerHeight:"100%",
           initialWidth:"64px",
           initialHeight:"84px",
           closeButton:true,
           iframe: true,
        });
    });

    $('#upload-preview #template').hide();
    $('#upload-preview').find('button.btn.delete').hide();
    
    //Enable Commenting
    enableComments();
    
    //Bind pusher channel
    pusherSubscribeCurrentPresenceChannel(true,true);    

    //Hide Loading Message
    messenger_hidenotiftop(); 

})();