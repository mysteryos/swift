/* 
 * Name: Freight company Edit Form
 */
(window.fc_edit = function() {
    //Turn on inline Mode
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.ajaxOptions = {type: "put"};

    //General Info
    $('.editable:not(.dummy)').editable();

    //Hide Loading Message
    messenger_hidenotiftop();  
})();
