(window.ot_storage_demurrage = function () {
    
    $('#content').on('click','tr[data-url] td',function(){
        $.pjax({
            url: $(this).parent('tr').attr('data-url'),
            container: '#main'
       });
    });    
    
    $('#storage_demurrage_calendar').html('');
    $('#storage_demurrage_calendar').fullCalendar({
        editable : false,
        draggable : false,
        selectable : true,
        selectHelper : true,
        unselectAuto : true,
        disableResizing : false,
        eventSources: [{
            url: document.getElementById('storage_demurrage_calendar').getAttribute('data-url'),
            type: 'POST',
        }],
        loading: function(isLoading,view) {
            if(isLoading)
            {
                $('#storage_demurrage_calendar').closest('.jarviswidget').children('header').append('<span class="jarviswidget-loader"><i class="fa fa-refresh fa-spin"></i></span>');
            }
            else
            {
                $('#storage_demurrage_calendar').closest('.jarviswidget').children('header').find('span.jarviswidget-loader').remove();
            }
        },
        eventRender: function(e, view) {
            view.popover({content:e.progress,
                            animation: true,
                            html: true,
                            placement: "auto",
                            trigger: 'hover',
                            container: '#main'});
        }
    });
    
    // calendar month
    $('#transit_calendar_mt').on('click',function() {
        $('#storage_demurrage_calendar').fullCalendar('changeView', 'month');
    });

    // calendar agenda week
    $('#transit_calendar_ag').on('click',function() {
        $('#storage_demurrage_calendar').fullCalendar('changeView', 'agendaWeek');
    });

    // calendar agenda day
    $('#transit_calendar_td').on('click',function() {
        $('#storage_demurrage_calendar').fullCalendar('changeView', 'agendaDay');
    });    
    
    //Hide Loading Message
    messenger_hidenotiftop();
})();
    