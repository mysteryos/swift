/**
 * Name: Order Tracking - Overview
 */

(window.ot_overview = function() {
    
    $(".js-status-update a").on('click',function() {
            var selText = $(this).text();
            var $this = $(this);
            $this.parents('.btn-group').find('.dropdown-toggle').html(selText + ' <span class="caret"></span>');
            $this.parents('.dropdown-menu').find('li').removeClass('active');
    });    
    
    //Setup calendar
    $('#transit_calendar').html('');
    $('#transit_calendar').fullCalendar({
        editable : false,
        draggable : false,
        selectable : true,
        selectHelper : true,
        unselectAuto : true,
        disableResizing : false,
        eventSources: [{
            url:'/order-tracking/transitcalendar',
            type: 'POST'
        }],
        loading: function(isLoading,view) {
            if(isLoading)
            {
                $('#transit_calendar').closest('.jarviswidget').children('header').append('<span class="jarviswidget-loader"><i class="fa fa-refresh fa-spin"></i></span>');
            }
            else
            {
                $('#transit_calendar').closest('.jarviswidget').children('header').find('span.jarviswidget-loader').remove();
            }
        },
        eventRender: function(e, view) {
            view.popover({content:e.vesselIcon + " " + e.vesselName + " - " + e.vesselVoyage,
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