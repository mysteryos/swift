/* 
 * Name: Dashboard
 */
(window.dashboard = function () {
    
    $('#content').on('click','tr[data-url] td',function(){
        $.pjax({
            url: $(this).parent('tr').attr('data-url'),
            container: '#main'
       });
    });
    
    var infiniteScrollContainer = ['#dashboard-latestworkflow-body','#dashboard-todolist-body'];
    
    for(var index = 0, len = infiniteScrollContainer.length; index < len; index++)
    {
        $(infiniteScrollContainer[index]).infinitescroll({
            behavior: 'local',
            binder: $(infiniteScrollContainer[index]), // scroll on this element rather than on the window
            nextSelector: infiniteScrollContainer[index]+" ul.pagination li a[rel='next']",
            navSelector: infiniteScrollContainer[index]+" ul.pagination",
            animate: true,
            debug: true,
            itemSelector: 'tr.post',
            contentSelector: infiniteScrollContainer[index]+' table tbody',
            loading: {
                finished: '',
                finishedMsg: "<em>Congratulations, you've reached the end of the internet.</em>",
                img: '',
                msg: $('<h2><i class="fa fa-refresh fa-spin"></i> Loading..</h2>'),
                msgText: '',
                speed: 'fast',
                start: undefined,
                selector: infiniteScrollContainer[index]+' div.infinite-scroll-loading'
            },        
        });        
    }
    
    //Hide Loading Message
    messenger_hidenotiftop();

})();