(window.ot_transit_local = function () {
    
    $('#content').on('click','tr[data-url] td',function(){
        $.pjax({
            url: $(this).parent('tr').attr('data-url'),
            container: '#main'
       });
    });    
    
    $('#transit_calendar_local').html('');
    $('#transit_calendar_local').fullCalendar({
        editable : false,
        draggable : false,
        selectable : true,
        selectHelper : true,
        unselectAuto : true,
        disableResizing : false,
        eventSources: [{
            url: document.getElementById('transit_calendar_local').getAttribute('data-url'),
            type: 'POST',
        }],
        loading: function(isLoading,view) {
            if(isLoading)
            {
                $('#transit_calendar_local').closest('.jarviswidget').children('header').append('<span class="jarviswidget-loader"><i class="fa fa-refresh fa-spin"></i></span>');
            }
            else
            {
                $('#transit_calendar_local').closest('.jarviswidget').children('header').find('span.jarviswidget-loader').remove();
            }
        },
        eventRender: function(e, view) {
            view.popover({content:e.vesselIcon + " - " + e.freightCompany,
                            animation: true,
                            html: true,
                            placement: "auto",
                            trigger: 'hover',
                            container: '#main'});
        }
    });
    
    // calendar month
    $('#transit_calendar_mt').on('click',function() {
            $('#transit_calendar').fullCalendar('changeView', 'month');
    });

    // calendar agenda week
    $('#transit_calendar_ag').on('click',function() {
            $('#transit_calendar').fullCalendar('changeView', 'agendaWeek');
    });

    // calendar agenda day
    $('#transit_calendar_td').on('click',function() {
            $('#transit_calendar').fullCalendar('changeView', 'agendaDay');
    });    
    
    //Hide Loading Message
    messenger_hidenotiftop();
})();
    