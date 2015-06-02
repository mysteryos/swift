/*
 * VARIABLES
 * Description: All Global Vars
 */
//JS loaded files
var jsArray = {};
//Container for Top message
var topmsg;
//Container for Upload message
var uploadmsg;
// Impacts the responce rate of some of the responsive elements (lower value affects CPU but improves speed)
$.throttle_delay = 350;

// The rate at which the menu expands revealing child elements on click
$.menu_speed = 235;

// Note: You will also need to change this variable in the "variable.less" file.
$.navbar_height = 49; 

/*
 * APP DOM REFERENCES
 * Description: Obj DOM reference, please try to avoid changing these
 */	
$.root_ = $('body');
$.document_ = $(document);
$.left_panel = $('#left-panel');
$.shortcut_dropdown = $('#shortcut');
$.bread_crumb = $('#ribbon ol.breadcrumb');
$.maindiv = $('#main');
$.leftsidemenu = $('#leftsidemenu');
$.notification_list = $('#notification-list');
$.notification_count = $('#activity-badge');

// desktop or mobile
$.device = null;

/*
 * APP CONFIGURATION
 * Description: Enable / disable certain theme features here
 */		
$.navAsAjax = false; // Your left nav in your app will no longer fire ajax calls

// Please make sure you have included "jarvis.widget.js" for this below feature to work
$.enableJarvisWidgets = true;

// Warning: Enabling mobile widgets could potentially crash your webApp if you have too many 
// 			widgets running at once (must have $.enableJarvisWidgets = true)
$.enableMobileWidgets = true;


/*
 * DETECT MOBILE DEVICES
 * Description: Detects mobile device - if any of the listed device is detected
 * a class is inserted to $.root_ and the variable $.device is decleard. 
 */	

/* so far this is covering most hand held devices */
var ismobile = (/iphone|ipad|ipod|android|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()));

if (!ismobile) {
        // Desktop
        $.root_.addClass("desktop-detected");
        $.device = "desktop";
} else {
        // Mobile
        $.root_.addClass("mobile-detected");
        $.device = "mobile";

        // Removes the tap delay in idevices
        // dependency: js/plugin/fastclick/fastclick.js 
        //FastClick.attach(document.body);
}

/*
 * PJAX Global Defaults
 */
$.pjax.defaults.timeout = 30000;
$.pjax.defaults.container = "#main";

/*
 * Pusher Main
 */

var pusher = new Pusher('d34044dc68acb4ac2833',{authEndpoint : '/pusher/auth'});
//Presence Channel for current page
var presenceChannelCurrent = presenceChannelUser = null;
//List of presence channels subscribed to
var presenceChannelCurrentSubscriptions = [];
        
/* ~ END: CHECK MOBILE DEVICE */

/*
 * Utility Functions
 */

String.prototype.ucfirst = function()
{
    return this.charAt(0).toUpperCase() + this.substr(1);
}

/*
 * Displays notification on top of page
 */
function messenger_notiftop(msg,type,hideafter)
{
    topmsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                          message: msg,
                          type: (typeof type === undefined ? 'info' : type),
                          id: 'notif-top',
                          hideAfter: (typeof hideafter != "undefined" ? hideafter : 3)
                        });
}

function messenger_hidenotiftop()
{
    if(typeof topmsg === "object")
    {
        topmsg.hide();
    }
}

/*
 * Pusher Global Channel
 */

function pusher_global()
{
    var presenceChannelGlobal = pusher.subscribe('presence-global');
    presenceChannelGlobal.bind('pusher:subscription_succeeded', function(users) {
        //Do Something here
    });
}

function executePageScript()
{
    if(typeof document.getElementById('content').getAttribute('data-js') !== null)
    {
        var functionName = document.getElementById('content').getAttribute('data-js').toString();
        if(typeof main[functionName] === "function")
        {
            main[functionName]();
        }
        else
        {
            //Try some magic
            if(typeof window[functionName] === "undefined")
            {
                var jsUrl = document.getElementById('content').getAttribute('data-urljs').toString();
                if(typeof jsUrl !== "undefined")
                {
                    jsLoader([$.trim(jsUrl)]);
                }
                else
                {
                    console.log('Js Url not set');
                    jsLoader(["/js/swift/swift."+functionName+".js"]);
                }
            }
            else
            {
                pageSetUp();                
                window[functionName]();
            }            
        }
    }
    else
    {
        messenger_hidenotiftop();
    }    
}

/*
 * Pusher Notification Channel
 */

function pusher_user()
{
    var presenceChannelUser = pusher.subscribe('private-user-'+document.getElementById('user_id').value);
    presenceChannelUser.bind('pusher:subscription_succeeded', function(users) {
        presenceChannelUser.bind('notification_new',function(data){
            $.notification_list.prepend(data.html);
            $.notification_list.find('abbr[data-livestamp]').livestamp($(this).attr('data-livestamp'));
            $.bigBox({
                title : '<a href="'+data.url+'" class="pjax">'+data.title+'</a>',
                content : data.content,
                color : data.color,
                icon : "fa fadeInLeft animated "+data.icon,
                timeout : 10000
            });
            $.notification_count.html(parseInt($.notification_count.html())+1).addClass('bg-color-red');
        });
        
       presenceChannelUser.bind('story_new',function(data){
            if(document.getElementById('timeline-list') !== null)
            {
                if(document.getElementById('timeline-list').getAttribute('data-context') !== "" && document.getElementById('timeline-list').getAttribute('data-context') === data.context)
                {
                    return false;
                }
                
                if($('#timeline-list').find('#post_'+data.id).length == 0)
                {
                    $('#timeline-list').prepend(data.html);
                }
            }
       });
    });
}

/*
 * Pusher Current Presence Channel
 */
function pusherSubscribeCurrentPresenceChannel(xeditable,multi_xeditable)
{
    presenceChannelCurrentSubscriptions.push('presence-'+document.getElementById('channel_name').value);
    presenceChannelCurrent = pusher.subscribe('presence-'+document.getElementById('channel_name').value);
    presenceChannelCurrent.bind('pusher:subscription_succeeded', function() {
        //Loop through members and add them to whos-online
        presenceChannelCurrent.members.each(function(member) {
            if(member.id != presenceChannelCurrent.members.me.id)
            {
                if(!$('.whos-online').children('#user_'+member.id).length)
                {
                    $('.whos-online').append(avatarHTML(member))
                    $('.whos-online .avatar').tooltip();
                }
            }
        });
        
        presenceChannelCurrent.bind('pusher:member_added',function(member){
            if(!$('.whos-online').children('#user_'+member.id).length)
            {
                $('.whos-online').append(avatarHTML(member))
                $('.whos-online .avatar').tooltip();
                //Play Sound
                var audioElement = document.createElement('audio');
                audioElement.setAttribute('src', $.sound_path + 'smallbox_1.mp3');
                audioElement.addEventListener("load", function () {
                    audioElement.play();
                }, true);
            }
        });
        
        presenceChannelCurrent.bind('pusher:member_removed',function(member){
            $('#user_'+member.id).tooltip('destroy');
            $('#user_'+member.id).remove();
        });
        
        presenceChannelCurrent.bind('html-update',function(data){
            var $element = $('#'+data.id);
            if($element.length)
            {
                $element.html(data.html);
            }
        });
        
        presenceChannelCurrent.bind('message',function(data){
            switch(data.message)
            {
                case 'small':
                    $.smallBox({
                                title : data.title,
                                content: data.content,
                                color : "#3276b1",
                                timeout : 4000
                    });                    
                    break;
                case 'big':
                    $.SmartMessageBox({
                            title : data.title,
                            content : data.content,
                            buttons : '[Ok]'
                    }); 
                    break;
            }
        });
        if(typeof xeditable !== "undefined" && xeditable)
        {
            presenceChannelCurrent.bind('pusher:member_added',function(member){
                var $editableOpen = $('.editable-open');
                if($editableOpen.length)
                {
                    presenceChannelCurrent.trigger('client-editable-shown',{user: presenceChannelCurrent.members.me , id: $editableOpen.attr('id')});
                }
            });
            presenceChannelCurrent.bind('pusher:member_removed',function(member){
                var $editableInUse = $('.editable[pusher-user="'+member.id+'"]');
                if($editableInUse.length)
                {
                    $editableInUse.tooltip('destroy');
                    $editableInUse.removeClass('editable-color-'+member.info.avatarColor);
                    $editableInUse.removeAttr('pusher-user');
                    $editableInUse.editable('enable');
                }
            });            
            presenceChannelCurrent.bind('client-editable-shown',function(data){
                //An editable has been opened by another user. Lock it down
                var $element = $("#"+data.id);
                if($element.length)
                {
                    $element.attr('pusher-user',data.user.id);
                    $element.addClass('editable-color-'+data.user.info.avatarColor);
                    $element.tooltip({title:'<i class="fa fa-edit" title="Editing"></i> '+data.user.info.name,
                                    animation: false,
                                    html: true,
                                    placement: "right",
                                    trigger: 'manual',
                                    template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner avatar-color-'+data.user.info.avatarColor+'"></div></div>'
                                    });
                    $element.tooltip('show');
                    $element.editable('disable');
                    $('#user_'+data.user.id).addClass('pulse');
                }
            });
            presenceChannelCurrent.bind('client-editable-hidden',function(data){
                //An editable has been closed by another user. open it up
                var $element = $("#"+data.id);
                $element.tooltip('hide');
                $element.tooltip('destroy');
                $element.removeClass('editable-color-'+data.user.info.avatarColor);
                $element.removeAttr('pusher-user');
                $element.editable('enable');
                $('#user_'+data.user.id).removeClass('pulse');
            });
            presenceChannelCurrent.bind('client-editable-save',function(data){
                //An Editable has changed value, reflect the change
                var $element = $("#"+data.id);
                if($element.attr('data-type')=="date")
                {
                    if(data.newValue === null)
                    {
                        $element.editable('setValue',null);
                    }
                    else
                    {
                        $element.editable('setValue',moment(data.newValue, "YYYY-MM-DDTHH:mm:ssZ").toDate(),true);                        
                    }
                }
                else
                {
                    $element.editable('setValue',data.newValue,true);
                }
                $element.effect("highlight", {color: "lightgreen"}, 1000);
            });
            if(typeof multi_xeditable !== "undefined" && multi_xeditable)
            {
                presenceChannelCurrent.bind('client-multi-add',function(data){
                    var $dummy = $('fieldset.dummy[data-name="'+data.context+'"]');
                    if($dummy.length)
                    {
                        addMulti($dummy,data.pk);
                        Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
                            showCloseButton: true,
                            type: 'info',
                            message: avatarHTML(data.user)+" has added "+data.context+" (ID:"+data.pk.id+") - <i>just now</i>",
                            hideAfter: 5
                        });                        
                    }
                });
                
                presenceChannelCurrent.bind('client-multi-delete',function(data){
                    var $editable = $('#'+data.id);
                    var $fieldset = $editable.parents('fieldset.multi');
                    if($fieldset.length)
                    {
                        Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
                            showCloseButton: true,
                            type: 'info',
                            message: avatarHTML(data.user)+" has deleted "+data.context+" (ID:"+data.id.split("_").slice(-1)[0]+") - <i>just now</i>",
                            hideAfter: 5
                        });
                        $fieldset.slideUp('500',function(){
                            $(this).remove();
                        });
                    }
                });                
            }
        }
    });
}

/*
 * JS Loader
 */

function jsLoader(files)
{
    $.each(files,function(key,val){
	if (!jsArray[val]) {
		jsArray[val] = true;
        }
        else
        {
            files.splice(key,1);
        }
    });
    
    if(files.length)
    {
        messenger_notiftop('Loading..','info',0);
        // Load form validation dependency 
        var myqueue = new createjs.LoadQueue(false);
        myqueue.maintainScriptOrder = true;
        myqueue.loadManifest(files);
        myqueue.on('complete',pageSetUp,this);
        myqueue.on("error",function(e){
            messenger_notiftop('Failed to load page. <a class="pjax" href="'+document.URL+'">Click here to reload</a>','error',0)
        });
    }
}

/*
 * Avatar HTML
 */

function avatarHTML(user)
{
    return '<i id="user_'+user.id+'" rel="tooltip" data-original-title="'+user.info.name+'" data-placement="bottom" class="avatar avatar-sm avatar-color-'+user.info.avatarColor+'">'+user.info.avatarLetter+'</i>';
}

function maintainRecentProject()
{
    //We have projects in the list
    //See if we need to add current project to list
    if(!!document.getElementById('project-url')&& !!document.getElementById('project-name') && !! document.getElementById('project-url'))
    {
        //project exists, shuffle to top
        if($('#project-context ul.dropdown-menu').find('a#project_'+document.getElementById('project-id').value).length)
        {
            var detached = $('#project-context ul.dropdown-menu').find('a#project_'+document.getElementById('project-id').value).parents('li.project').detach();
            $('#project-context ul.dropdown-menu').prepend(detached);
        }
        else
        {
            $('#project-context ul.dropdown-menu').prepend('<li class="project"><a id="project_'+document.getElementById('project-id').value+'" href="'+document.getElementById('project-url').value+'" class="pjax">'+document.getElementById('project-name').value+'</a></li>');
            $('#project-context ul.dropdown-menu li.project:gt(4)').remove();
        }

    }
}

function clearRecentProject()
{
    $('#project-context ul.dropdown-menu li.project').remove();
}

/*
 * RESIZER WITH THROTTLE
 * Source: http://benalman.com/code/projects/jquery-resize/examples/resize/
 */

(function($, window, undefined) {

	var elems = $([]), jq_resize = $.resize = $.extend($.resize, {}), timeout_id, str_setTimeout = 'setTimeout', str_resize = 'resize', str_data = str_resize + '-special-event', str_delay = 'delay', str_throttle = 'throttleWindow';

	jq_resize[str_delay] = $.throttle_delay;

	jq_resize[str_throttle] = true;

	$.event.special[str_resize] = {

		setup : function() {
			if (!jq_resize[str_throttle] && this[str_setTimeout]) {
				return false;
			}

			var elem = $(this);
			elems = elems.add(elem);
			$.data(this, str_data, {
				w : elem.width(),
				h : elem.height()
			});
			if (elems.length === 1) {
				loopy();
			}
		},
		teardown : function() {
			if (!jq_resize[str_throttle] && this[str_setTimeout]) {
				return false;
			}

			var elem = $(this);
			elems = elems.not(elem);
			elem.removeData(str_data);
			if (!elems.length) {
				clearTimeout(timeout_id);
			}
		},

		add : function(handleObj) {
			if (!jq_resize[str_throttle] && this[str_setTimeout]) {
				return false;
			}
			var old_handler;

			function new_handler(e, w, h) {
				var elem = $(this), data = $.data(this, str_data);
				data.w = w !== undefined ? w : elem.width();
				data.h = h !== undefined ? h : elem.height();

				old_handler.apply(this, arguments);
			};
			if ($.isFunction(handleObj)) {
				old_handler = handleObj;
				return new_handler;
			} else {
				old_handler = handleObj.handler;
				handleObj.handler = new_handler;
			}
		}
	};

	function loopy() {
		timeout_id = window[str_setTimeout](function() {
			elems.each(function() {
				var elem = $(this), width = elem.width(), height = elem.height(), data = $.data(this, str_data);
				if (width !== data.w || height !== data.h) {
					elem.trigger(str_resize, [data.w = width, data.h = height]);
				}

			});
			loopy();

		}, jq_resize[str_delay]);

	};

})(jQuery, this);

/*
* NAV OR #LEFT-BAR RESIZE DETECT
* Description: changes the page min-width of #CONTENT and NAV when navigation is resized.
* This is to counter bugs for min page width on many desktop and mobile devices.
* Note: This script uses JSthrottle technique so don't worry about memory/CPU usage
*/


/*
 * CUSTOM MENU PLUGIN
 */

$.fn.extend({

	//pass the options variable to the function
	jarvismenu : function(options) {

		var defaults = {
			accordion : 'true',
			speed : 200,
			closedSign : '[+]',
			openedSign : '[-]'
		};

		// Extend our default options with those provided.
		var opts = $.extend(defaults, options);
		//Assign current element to variable, in this case is UL element
		var $this = $(this);

		//add a mark [+] to a multilevel menu
		$this.find("li").each(function() {
			if ($(this).find("ul").size() != 0) {
				//add the multilevel sign next to the link
				$(this).find("a:first").append("<b class='collapse-sign'>" + opts.closedSign + "</b>");

				//avoid jumping to the top of the page when the href is an #
				if ($(this).find("a:first").attr('href') == "#") {
					$(this).find("a:first").click(function() {
						return false;
					});
				}
			}
		});

		//open active level
		$this.find("li.active").each(function() {
			$(this).parents("ul").slideDown(opts.speed);
			$(this).parents("ul").parent("li").find("b:first").html(opts.openedSign);
			$(this).parents("ul").parent("li").addClass("open")
		});

		$this.find("li a").click(function() {

			if ($(this).parent().find("ul").size() != 0) {

				if (opts.accordion) {
					//Do nothing when the list is open
					if (!$(this).parent().find("ul").is(':visible')) {
						parents = $(this).parent().parents("ul");
						visible = $this.find("ul:visible");
						visible.each(function(visibleIndex) {
							var close = true;
							parents.each(function(parentIndex) {
								if (parents[parentIndex] == visible[visibleIndex]) {
									close = false;
									return false;
								}
							});
							if (close) {
								if ($(this).parent().find("ul") != visible[visibleIndex]) {
									$(visible[visibleIndex]).slideUp(opts.speed, function() {
										$(this).parent("li").find("b:first").html(opts.closedSign);
										$(this).parent("li").removeClass("open");
									});

								}
							}
						});
					}
				}// end if
				if ($(this).parent().find("ul:first").is(":visible") && !$(this).parent().find("ul:first").hasClass("active")) {
					$(this).parent().find("ul:first").slideUp(opts.speed, function() {
						$(this).parent("li").removeClass("open");
						$(this).parent("li").find("b:first").delay(opts.speed).html(opts.closedSign);
					});

				} else {
					$(this).parent().find("ul:first").slideDown(opts.speed, function() {
						/*$(this).effect("highlight", {color : '#616161'}, 500); - disabled due to CPU clocking on phones*/
						$(this).parent("li").addClass("open");
						$(this).parent("li").find("b:first").delay(opts.speed).html(opts.openedSign);
					});
				} // end else
			} // end if
		});
	} // end function
});

/* ~ END: CUSTOM MENU PLUGIN */

/*
 * ELEMENT EXIST OR NOT
 * Description: returns true or false
 * Usage: $('#myDiv').doesExist();
 */

jQuery.fn.doesExist = function() {
	return jQuery(this).length > 0;
};

/* ~ END: ELEMENT EXIST OR NOT */

 /*
         * REMOVE TABLE ROW
        */ 

        (function() {
                var $;

                $ = jQuery;

                $.fn.extend({
                        rowslide : function(callback) {
                                var $row, $tds, highestTd;
                                $row = this;
                                $tds = this.find("td");
                                $row_id = $row.attr("id");
                                highestTd = this.getTallestTd($tds);
                                return $row.animate({
                                        opacity : 0
                                }, 80, function() {
                                        var $td, $wrapper, _this = this;
                                        $tds.each(function(i, td) {
                                                if (this !== highestTd) {
                                                        $(this).empty();
                                                        return $(this).css("padding", "0");
                                                }
                                        });
                                        $td = $(highestTd);
                                        $wrapper = $("<div/>");
                                        $wrapper.css($td.css("padding"));
                                        $td.css("padding", "0");
                                        $td.wrapInner($wrapper);
                                        return $td.children("div").animate({
                                                height : "hide"
                                        }, 100, "swing", function() {
                                                $row.remove();
                                                //console.log($row.attr("id") +" was deleted");
                                                if (callback) {
                                                        return callback();
                                                }
                                        });
                                });
                        },
                        getTallestTd : function($tds) {
                                var height, index;
                                index = -1;
                                height = 0;
                                $tds.each(function(i, td) {
                                        if ($(td).height() > height) {
                                                index = i;
                                                return height = $(td).height();
                                        }
                                });
                                return $tds.get(index);
                        }
                });

        }).call(this); 

        /* ~ END: TABLE REMOVE ROW */
/*
 * FULL SCREEN FUNCTION
 */

// Find the right method, call on correct element
function launchFullscreen(element) {

	if (!$.root_.hasClass("full-screen")) {

		$.root_.addClass("full-screen");

		if (element.requestFullscreen) {
			element.requestFullscreen();
		} else if (element.mozRequestFullScreen) {
			element.mozRequestFullScreen();
		} else if (element.webkitRequestFullscreen) {
			element.webkitRequestFullscreen();
		} else if (element.msRequestFullscreen) {
			element.msRequestFullscreen();
		}

	} else {
		
		$.root_.removeClass("full-screen");
		
		if (document.exitFullscreen) {
			document.exitFullscreen();
		} else if (document.mozCancelFullScreen) {
			document.mozCancelFullScreen();
		} else if (document.webkitExitFullscreen) {
			document.webkitExitFullscreen();
		}

	}

}

/*
 * ~ END: FULL SCREEN FUNCTION
 */

/*
 * INITIALIZE FORMS
 * Description: Select2, Masking, Datepicker, Autocomplete
 */

function runAllForms() {

	/*
	 * AJAX BUTTON LOADING TEXT
	 * Usage: <button type="button" data-loading-text="Loading..." class="btn btn-xs btn-default ajax-refresh"> .. </button>
	 */
	$('button[data-loading-text]').on('click', function() {
		var btn = $(this)
		btn.button('loading')
		setTimeout(function() {
			btn.button('reset')
		}, 3000)
	});

}

/* ~ END: INITIALIZE FORMS */

/*
 * INITIALIZE JARVIS WIDGETS
 */

// Setup Desktop Widgets
function setup_widgets_desktop() {
        
	if ($.fn.jarvisWidgets && $.enableJarvisWidgets) {

		$('#widget-grid').jarvisWidgets({

			grid : 'article',
			widgets : '.jarviswidget',
			localStorage : true,
			deleteSettingsKey : '#deletesettingskey-options',
			settingsKeyLabel : 'Reset settings?',
			deletePositionKey : '#deletepositionkey-options',
			positionKeyLabel : 'Reset position?',
			sortable : true,
			buttonsHidden : false,
			// toggle button
			toggleButton : true,
			toggleClass : 'fa fa-minus | fa fa-plus',
			toggleSpeed : 200,
			onToggle : function() {
			},
			// delete btn
			deleteButton : true,
			deleteClass : 'fa fa-times',
			deleteSpeed : 200,
			onDelete : function() {
			},
			// edit btn
			editButton : true,
			editPlaceholder : '.jarviswidget-editbox',
			editClass : 'fa fa-cog | fa fa-save',
			editSpeed : 200,
			onEdit : function() {
			},
			// color button
			colorButton : true,
			// full screen
			fullscreenButton : true,
			fullscreenClass : 'fa fa-expand | fa fa-compress',
			fullscreenDiff : 3,
			onFullscreen : function() {
			},
			// custom btn
			customButton : false,
			customClass : 'folder-10 | next-10',
			customStart : function() {
				alert('Hello you, this is a custom button...')
			},
			customEnd : function() {
				alert('bye, till next time...')
			},
			// order
			buttonOrder : '%refresh% %custom% %edit% %toggle% %fullscreen% %delete%',
			opacity : 1.0,
			dragHandle : '> header',
			placeholderClass : 'jarviswidget-placeholder',
			indicator : true,
			indicatorTime : 600,
			ajax : true,
			timestampPlaceholder : '.jarviswidget-timestamp',
			timestampFormat : 'Last update: %m%/%d%/%y% %h%:%i%:%s%',
			refreshButton : true,
			refreshButtonClass : 'fa fa-refresh',
			labelError : 'Sorry but there was a error:',
			labelUpdated : 'Last Update:',
			labelRefresh : 'Refresh',
			labelDelete : 'Delete widget:',
			afterLoad : function() {
                            $(this).find('abbr[data-livestamp]').each(function(){
                                $(this).livestamp(parseInt(this.getAttribute('data-livestamp')));
                            });
			},
			rtl : false, // best not to toggle this!
			onChange : function() {
				
			},
			onSave : function() {
				
			},
			ajaxnav : $.navAsAjax // declears how the localstorage should be saved

		});

	}

}

// Setup Desktop Widgets
function setup_widgets_mobile() {

	if ($.enableMobileWidgets && $.enableJarvisWidgets) {
		setup_widgets_desktop();
	}

}

/* ~ END: INITIALIZE JARVIS WIDGETS */

/*
 * GOOGLE MAPS
 * description: Append google maps to head dynamically
 */

var gMapsLoaded = false;
window.gMapsCallback = function() {
	gMapsLoaded = true;
	$(window).trigger('gMapsLoaded');
}
window.loadGoogleMaps = function() {
	if (gMapsLoaded)
		return window.gMapsCallback();
	var script_tag = document.createElement('script');
	script_tag.setAttribute("type", "text/javascript");
	script_tag.setAttribute("src", "http://maps.google.com/maps/api/js?sensor=false&callback=gMapsCallback");
	(document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
}
/* ~ END: GOOGLE MAPS */

/*
 * LOAD SCRIPTS
 * Usage:
 * Define function = myPrettyCode ()...
 * loadScript("js/my_lovely_script.js", myPrettyCode);
 */

var jsArray = {};

function loadScript(scriptName, callback) {

	if (!jsArray[scriptName]) {
		jsArray[scriptName] = true;
                
		// adding the script tag to the head as suggested before
		var body = document.getElementsByTagName('body')[0];
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.src = scriptName;

		// then bind the event to the callback function
		// there are several events for cross browser compatibility
		//script.onreadystatechange = callback;
		script.onload = callback;

		// fire the loading
		body.appendChild(script);

	} else if (callback) {// changed else to else if(callback)
		//console.log("JS file already added!");
		//execute function
		callback();
	}

}

function enableComments()
{
    var $commentForm = $('#commentsContainer');
    if($commentForm.length)
    {
        //Tagging users
        $('#comment-textarea').atwho({
            at: "@",
            data: '/ajaxsearch/userall',
            search_key: 'name',
            limit: 5,
            delay: 300,
            tpl: '<li data-value="${name}">${name} <small>${email}</small></li>',
            insert_tpl: '<input type="button" value="@${name}" data-id="${id}" title="${email}" class="usermention btn btn-default btn-xs" />',
            show_the_at: true
                    
        });        
        
        //Send Comment
        $commentForm.on('submit',function(e){
            
            if($.trim($('#comment-textarea').text()) !== "")
            {
                e.preventDefault();
                
                var $formdata = $commentForm.serialize();
                var $usermention = $('#comment-textarea').find('.usermention').map(function(){
                                        return $(this).attr('data-id');
                                    }).get();
                $formdata += "&usermention="+encodeURIComponent($usermention);
                //Find all user tags and convert to text
                var $comment = $('#comment-textarea').clone();
                $comment.find('.usermention').each(function(){
                   $(this).append('<span>'+this.attributes.value.value+'</span>');
                });
                //Append to formdata
                $formdata += "&comment="+encodeURIComponent($comment.text());
                
                //Disable the buttons
                $('#comment-textarea').attr('disabled','disabled');
                $('#comment-submit').attr('disabled','disabled');
                
                //Send to server
                Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
                    id: 'notif-top',
                    errorMessage: 'Error posting comment',
                    successMessage: 'Comment posted',
                    progressMessage: 'Posting comment..',
                    action: $.ajax,
                },
                {
                    type:'POST',
                    url: $commentForm.attr('action'),
                    data: $formdata,
                    success:function()
                    {
                        if(presenceChannelCurrent && document.getElementById('channel_name') !== null)
                        {
                            presenceChannelCurrent.trigger('client-new-chat',{user: presenceChannelCurrent.members.me, msg: encodeURIComponent($comment.text()), mentions: encodeURIComponent($usermention)});
                        }
                        
                        $('#chat-body').load('/comment/listcomment/'+document.getElementById('commentable_key').value,function(){
                            $('#comment-textarea').html('');
                            $('#comment-textarea').removeAttr('disabled');
                            $('#comment-submit').removeAttr('disabled');                              
                        });
                  
                    },
                    error:function(xhr, status, error)
                    {
                        $('#comment-textarea').removeAttr('disabled');
                        $('#comment-submit').removeAttr('disabled');                      
                        return xhr.responseText;
                    }
                });
            }
            else
            {
                alert('You cannot send an empty comment');
            }
            return false;
        });
        
        if(presenceChannelCurrent && document.getElementById('channel_name') !== null)
        {
            presenceChannelCurrent.bind('client-new-chat',function(data){
                $('#chat-body').load('/comment/listcomment/'+document.getElementById('commentable_key').value,function(){
                    Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
                        showCloseButton: true,
                        type: 'info',
                        message: avatarHTML(data.user)+" has commented \""+data.msg+"\" - <i>just now</i>",
                        hideAfter: 10
                    });                      
                });                
            });
        }
    }
}

/* ~ END: LOAD SCRIPTS */

/*
* APP AJAX REQUEST SETUP
* Description: Executes and fetches all ajax requests also
* updates naivgation elements to active
*/
if($.navAsAjax)
{
    // fire this on page load if nav exists
    if ($('nav').length) {
	    checkURL();
    };

    $(document).on('click', 'nav a[href!="#"]', function(e) {
	    e.preventDefault();
	    var $this = $(e.currentTarget);

	    // if parent is not active then get hash, or else page is assumed to be loaded
		if (!$this.parent().hasClass("active") && !$this.attr('target')) {

		    // update window with hash
		    // you could also do here:  $.device === "mobile" - and save a little more memory

		    if ($.root_.hasClass('mobile-view-activated')) {
			    $.root_.removeClass('hidden-menu');
			    window.setTimeout(function() {
					if (window.location.search) {
						window.location.href =
							window.location.href.replace(window.location.search, '')
								.replace(window.location.hash, '') + '#' + $this.attr('href');
					} else {
						window.location.hash = $this.attr('href')
					}
			    }, 150);
			    // it may not need this delay...
		    } else {
				if (window.location.search) {
					window.location.href =
						window.location.href.replace(window.location.search, '')
							.replace(window.location.hash, '') + '#' + $this.attr('href');
				} else {
					window.location.hash = $this.attr('href');
				}
		    }
	    }

    });

    // fire links with targets on different window
    $(document).on('click', 'nav a[target="_blank"]', function(e) {
	    e.preventDefault();
	    var $this = $(e.currentTarget);

	    window.open($this.attr('href'));
    });

    // fire links with targets on same window
    $(document).on('click', 'nav a[target="_top"]', function(e) {
	    e.preventDefault();
	    var $this = $(e.currentTarget);

	    window.location = ($this.attr('href'));
    });

    // all links with hash tags are ignored
    $(document).on('click', 'nav a[href="#"]', function(e) {
	    e.preventDefault();
    });

    // DO on hash change
    $(window).on('hashchange', function() {
	    checkURL();
    });
}

// CHECK TO SEE IF URL EXISTS
function checkURL() {

	//get the url by removing the hash
	var url = location.hash.replace(/^#/, '');

	container = $('#content');
	// Do this if url exists (for page refresh, etc...)
	if (url) {
		// remove all active class
		$('nav li.active').removeClass("active");
		// match the url and add the active class
		$('nav li:has(a[href="' + url + '"])').addClass("active");
		var title = ($('nav a[href="' + url + '"]').attr('title'))

		// change page title from global var
		document.title = (title || document.title);
		//console.log("page title: " + document.title);

		// parse url to jquery
		loadURL(url + location.search, container);
	} else {

		// grab the first URL from nav
		var $this = $('nav > ul > li:first-child > a[href!="#"]');

		//update hash
		window.location.hash = $this.attr('href');

	}

}

// LOAD AJAX PAGES

function loadURL(url, container) {
	//console.log(container)

	$.ajax({
		type : "GET",
		url : url,
		dataType : 'html',
		cache : true, // (warning: this will cause a timestamp and will call the request twice)
		beforeSend : function() {
			// cog placed
			container.html('<h1><i class="fa fa-cog fa-spin"></i> Loading...</h1>');
		
			// Only draw breadcrumb if it is main content material
			// TODO: see the framerate for the animation in touch devices
			
			if (container[0] == $("#content")[0]) {
				drawBreadCrumb();
				// scroll up
				$("html").animate({
					scrollTop : 0
				}, "fast");
			} 
		},
		/*complete: function(){
	    	// Handle the complete event
	    	// alert("complete")
		},*/
		success : function(data) {
			// cog replaced here...
			// alert("success")
			
			container.css({
				opacity : '0.0'
			}).html(data).delay(50).animate({
				opacity : '1.0'
			}, 300);
			

		},
		error : function(xhr, ajaxOptions, thrownError) {
			container.html('<h4 style="margin-top:10px; display:block; text-align:left"><i class="fa fa-warning txt-color-orangeDark"></i> Error 404! Page not found.</h4>');
		},
		async : false
	});

	//console.log("ajax request sent");
}

//PJAX Setup

/*
 * Bind links with pjax class
 */
$.document_.pjax('a.pjax', '#main');

/*
 * Color box AJAX Universal
 */

$.maindiv.on('click','a.colorbox-ajax',function(){
     $.colorbox({
        href:$(this).attr('href'),
        open:true,
        maxHeight:"100%",
        maxWidth:"100%",
        initialWidth:"64px",
        initialHeight:"84px",
        closeButton:false,
        transition:"fade"
    });
    return false; 
});

/*
 * After HTML Replace
 */
$.document_.on('pjax:success, pjax:end',function(){
    //Execute Javascript
    $('.popover, .tooltip').remove();
    executePageScript();
    //Check Site Version
    if($.root_.attr('data-version') !== document.getElementById('site_version').value)
    {
        $.smallBox({
                title : "System Update Available",
                content: "Refresh page & apply update? <p class='text-align-right'><a href='javascript:void(0);' onclick='window.location.reload();' class='btn btn-primary btn-sm'>Yes</a> <a href='javascript:void(0);' class='btn btn-danger btn-sm'>No</a></p>",
                color : "#F89406",
                icon : "fa fa-bolt"
        });        
    }
});

$.document_.on('pjax:beforeSend',function(){
    messenger_notiftop('Loading..','info',0);
});

//PJAX

$.document_.on('pjax:beforeReplace',function(){
   if(presenceChannelCurrent && document.getElementById('channel_name') !== null)
   {
       presenceChannelCurrent.unsubscribe('presence-'+document.getElementById('channel_name').value);
   }
});

/* ~ END: APP AJAX REQUEST SETUP */

/* Search Bar Setup */
var allBloodhound = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
        url: '/search/all/%QUERY',
        ajax: {
            error: function(xhr,text,error)
            {
                messenger_notiftop(xhr.responseText,"error",5);
            }
        }      
  },

});

allBloodhound.initialize();
allBloodhound.clearRemoteCache();

$('#search-fld').typeahead(null, {
    name: 'all',
    displayKey: 'value',
    source: allBloodhound.ttAdapter(),
    highlight: true,
    templates: {
      empty: [
        '<div class="empty-message">',
        'No suggestions for your search',
        '</div>'
      ].join('\n'),
      suggestion: Handlebars.compile('<p><div>{{{highlight}}}</div><div><i class="fa fa-fw {{icon}}" title="{{title}}"/><a href="{{url}}" class="pjax">{{value}}</a></div></p>')
    }    
}).on('typeahead:selected', function(event, selection) {
    $.pjax({
       url:selection.url
    });
});

$('form.header-search').on('submit',function(){
    var $this = $(this);
    $.pjax ({
          container: '#main',
          timeout: 10000,
          url: '/search',
          data: $this.serialize()
    });
    return false;
});

/* End: Search Bar Setup */

/*Form Validation Setup*/

// override jquery validate plugin defaults
$.validator.setDefaults({
    highlight: function(element) {
        $(element).closest('.form-group').addClass('has-error');
    },
    unhighlight: function(element) {
        $(element).closest('.form-group').removeClass('has-error');
    },
    errorElement: 'span',
    errorClass: 'help-block',
    errorPlacement: function(error, element) {
        if(element.parent('.input-group').length) {
            error.insertAfter(element.parent());
        } else {
            error.insertAfter(element);
        }
    }
});

/*Form Validation Setup END*/

/*
 * PAGE SETUP
 * Description: fire certain scripts that run through the page
 * to check for form elements, tooltip activation, popovers, etc...
 */
function pageSetUp() {

	if ($.device === "desktop"){
		// is desktop
		
		// activate tooltips
		$("[rel=tooltip]").tooltip();
	
		// activate popovers
		$("[rel=popover]").popover();
	
		// activate popovers with hover states
		$("[rel=popover-hover]").popover({
			trigger : "hover"
		});
                
                //Abbr TimeStamps
                $('abbr[data-livestamp]').each(function(){
                    $(this).livestamp(parseInt(this.getAttribute('data-livestamp')));
                });
	
		// setup widgets
		setup_widgets_desktop();
	
		//setup nav height (dynamic)
		nav_page_height();
	
                //set Recent projects
                maintainRecentProject();
                $('#clear-project').on('click',function(){
                   clearRecentProject(); 
                });

	} else {
		
		// is mobile
		
		// activate popovers
		$("[rel=popover]").popover();
	
		// activate popovers with hover states
		$("[rel=popover-hover]").popover({
			trigger : "hover"
		});
                
                //Abbr TimeStamps
                $('abbr[data-livestamp]').livestamp($(this).attr('data-livestamp'));
	
		// setup widgets
		setup_widgets_mobile();
	
		//setup nav height (dynamic)
		nav_page_height();
		
	}

}

/*
 * DOCUMENT LOADED EVENT
 * Description: Fire when DOM is ready
 */
/*
 * Fire tooltips
 */
if ($("[rel=tooltip]").length) {
        $("[rel=tooltip]").tooltip();
}

//TODO: was moved from window.load due to IE not firing consist
nav_page_height()

// INITIALIZE LEFT NAV
if (!null) {
        $.leftsidemenu.children('ul').jarvismenu({
                accordion : true,
                speed : $.menu_speed,
                closedSign : '<em class="fa fa-expand-o"></em>',
                openedSign : '<em class="fa fa-collapse-o"></em>'
        });

        $.leftsidemenu.on('click','a',function(){
           $('nav li.active').removeClass("active");
           $(this).parent('li').addClass('active');
        });
} else {
        alert("Error - menu anchor does not exist");
}

// COLLAPSE LEFT NAV
$('.minifyme').click(function(e) {
        $('body').toggleClass("minified");
        $(this).effect("highlight", {}, 500);
        e.preventDefault();
});

// HIDE MENU
$('#hide-menu >:first-child > a').click(function(e) {
        $('body').toggleClass("hidden-menu");
        e.preventDefault();
});

//	$('#show-shortcut').click(function(e) {
//		if ($.shortcut_dropdown.is(":visible")) {
//			shortcut_buttons_hide();
//		} else {
//			shortcut_buttons_show();
//		}
//		e.preventDefault();
//	});

// SHOW & HIDE MOBILE SEARCH FIELD
$('#search-mobile').click(function() {
        $.root_.addClass('search-mobile');
});

$('#cancel-search-js').click(function() {
        $.root_.removeClass('search-mobile');
});

// ACTIVITY
// ajax drop
$('#activity').click(function(e) {
        var $this = $(this);

        if ($this.find('.badge').hasClass('bg-color-red')) {
                $this.find('.badge').removeClassPrefix('bg-color-');
                $this.find('.badge').text("0");
                $.ajax({
                   url: '/notification/markread',

                });
        }

        if (!$this.next('.ajax-dropdown').is(':visible')) {
                $this.next('.ajax-dropdown').fadeIn(150);
                $this.addClass('active');
        } else {
                $this.next('.ajax-dropdown').fadeOut(150);
                $this.removeClass('active')
        }

        var mytest = $this.next('.ajax-dropdown').find('.btn-group > .active > input').attr('id');
        //console.log(mytest)

        e.preventDefault();
});

$('input[name="activity"]').change(function() {
        //alert($(this).val())
        var $this = $(this);

        url = $this.attr('id');
        container = $('.ajax-notifications');

        loadURL(url, container);

});

$(document).mouseup(function(e) {
        if (!$('.ajax-dropdown').is(e.target)// if the target of the click isn't the container...
        && $('.ajax-dropdown').has(e.target).length === 0) {
                $('.ajax-dropdown').fadeOut(150);
                $('.ajax-dropdown').prev().removeClass("active")
        }
});

$('button[data-loading-text]').on('click', function() {
        var btn = $(this)
        btn.button('loading')
        setTimeout(function() {
                btn.button('reset')
        }, 3000)
});

// NOTIFICATION IS PRESENT

function notification_check() {
        $this = $('#activity > .badge');

        if (parseInt($this.text()) > 0) {
                $this.addClass("bg-color-red bounceIn animated")
        }
}

notification_check();

// RESET WIDGETS
$('#refresh').click(function(e) {
        $.SmartMessageBox({
                title : "<i class='fa fa-refresh' style='color:green'></i> Clear Local Storage",
                content : "Would you like to RESET all your saved widgets and clear LocalStorage?",
                buttons : '[No][Yes]'
        }, function(ButtonPressed) {
                if (ButtonPressed == "Yes" && localStorage) {
                        localStorage.clear();
                        location.reload();
                }

        });
        e.preventDefault();
});

// LOGOUT BUTTON
$('#logout a').on('click',function(e) {
        e.preventDefault();            
        e.stopPropagation();
        //get the link
        var $this = $(this);
        $.loginURL = $this.attr('href');
        $.logoutMSG = $this.data('logout-msg');

        // ask verification
        return $.SmartMessageBox({
                title : "<i class='fa fa-sign-out txt-color-orangeDark'></i> Logout <span class='txt-color-orangeDark'><strong>" + $.trim($('#show-shortcut').find('span.text').html()) + "</strong></span> ?",
                content : $.logoutMSG || "You can improve your security further after logging out by closing this opened browser",
                buttons : '[No][Yes]'

        }, function(ButtonPressed) {
                if (ButtonPressed == "Yes") {
                        $.root_.addClass('animated fadeOutUp');
                        window.location = $.loginURL;
                }
                else
                {
                    return false;
                }

        });
});

/*
 * Pusher User Channel
 */

pusher_user();

/*
* SHORTCUTS
*/

// SHORT CUT (buttons that appear when clicked on user name)
$.shortcut_dropdown.find('a').click(function(e) {

        e.preventDefault();

        window.location = $(this).attr('href');
        setTimeout(shortcut_buttons_hide, 300);

});

// SHORTCUT buttons goes away if mouse is clicked outside of the area
$(document).mouseup(function(e) {
        if (!$.shortcut_dropdown.is(e.target)// if the target of the click isn't the container...
        && $.shortcut_dropdown.has(e.target).length === 0) {
                shortcut_buttons_hide()
        }
});

// SHORTCUT ANIMATE HIDE
function shortcut_buttons_hide() {
        $.shortcut_dropdown.animate({
                height : "hide"
        }, 300, "easeOutCirc");
        $.root_.removeClass('shortcut-on');

}

// SHORTCUT ANIMATE SHOW
function shortcut_buttons_show() {
        $.shortcut_dropdown.animate({
                height : "show"
        }, 200, "easeOutCirc")
        $.root_.addClass('shortcut-on');
}

// Fix page and nav height
function nav_page_height() {
	var setHeight = $('#main').height();
	//menuHeight = $.left_panel.height();
	
	var windowHeight = $(window).height() - $.navbar_height;
	//set height

	if (setHeight > windowHeight) {// if content height exceedes actual window height and menuHeight
		$.left_panel.css('min-height', setHeight + 'px');
		$.root_.css('min-height', setHeight + $.navbar_height + 'px');

	} else {
		$.left_panel.css('min-height', windowHeight + 'px');
		$.root_.css('min-height', windowHeight + 'px');
	}
}

$('#main').resize(function() {
	nav_page_height();
	check_if_mobile_width();
})

$('nav').resize(function() {
	nav_page_height();
})

function check_if_mobile_width() {
	if ($(window).width() < 979) {
		$.root_.addClass('mobile-view-activated')
	} else if ($.root_.hasClass('mobile-view-activated')) {
		$.root_.removeClass('mobile-view-activated');
	}
}

/* ~ END: NAV OR #LEFT-BAR RESIZE DETECT */


// Keep only 1 active popover per trigger - also check and hide active popover if user clicks on document
$('body').on('click', function(e) {
	$('[rel="popover"]').each(function() {
		//the 'is' for buttons that trigger popups
		//the 'has' for icons within a button that triggers a popup
		if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
			$(this).popover('hide');
		}
	});
});

$.maindiv.on('click','.btn-togglesubscribe',function(e){
    e.preventDefault();
    var $this = $(this);
    $this.attr('disabled','disabled');
    $this.addClass('loading-animation');
    
    $.ajax({
        url: $this.attr('data-href'),
        type: 'PUT',
        dataType: 'json',
        success:function(resultJson)
        {
            $.smallBox({
                    title : "Subscription",
                    content : (resultJson.result === 1 ? "You have been subscribed successfully" : "You have been unsubscribed successfully"),
                    color : "#5384AF",
                    icon : "fa "+(resultJson.result === 1 ? "fa-heart" : "fa-heart-o"),
                    timeout : 3000
            });
            
            $this.find('i.fa').toggle();
            
            $this.removeAttr('disabled');
            $this.removeClass('loading-animation');                
        },
        error:function(xhr, status, error)
        {
            $this.removeAttr('disabled');
            $this.removeClass('loading-animation');
            return xhr.responseText;
        }
    });    
});

/*
 * Custom Script Loaders
 */

var main = {
    /*
     * Dashboard Index Js
     */
    dashboard: function () {
        if(typeof window['dashboard'] === "undefined")
        {
            jsLoader(['/js/plugin/jquery-infinitescroll/jquery.infinitescroll.min.js', '/js/swift/swift.dashboard.js']);            
        }
        else
        {
            pageSetUp();
            dashboard();
        }
    },
    
    /*
     * Inbox Index JS
     */
    
    inbox: function () {
        // DO NOT REMOVE : GLOBAL FUNCTIONS!
        pageSetUp();

        // PAGE RELATED SCRIPTS
        
        /*
         * Fixed table height
         */

        tableHeightSize();
        
        $(window).resize(function() {
                tableHeightSize()
        })

        function tableHeightSize() {

                var tableHeight = $(window).height() - 212;

                if (tableHeight < 320) {
                        $('.table-wrap').css('height', 320 + 'px');
                } else {
                        $('.table-wrap').css('height', tableHeight + 'px');
                }

        }
        
	//Gets tooltips activated
	$("#inbox-table [rel=tooltip]").tooltip();

	$("#inbox-table input[type='checkbox']").change(function() {
		$(this).closest('tr').toggleClass("highlight", this.checked);
	});

	$("#inbox-table .inbox-data-message").click(function() {
		$this = $(this);
		getMail($this);
	})
	$("#inbox-table .inbox-data-from").click(function() {
		$this = $(this);
		getMail($this);
	})
	function getMail($this) {
		//console.log($this.closest("tr").attr("id"));
		loadURL("ajax/email-opened.html", $('#inbox-content > .table-wrap'));
	}


	$('.inbox-table-icon input:checkbox').click(function() {
		enableDeleteButton();
	})

	$(".deletebutton").click(function() {
		$('#inbox-table td input:checkbox:checked').parents("tr").rowslide();
		//$(".inbox-checkbox-triggered").removeClass('visible');
		//$("#compose-mail").show();
	});

	function enableDeleteButton() {
		var isChecked = $('.inbox-table-icon input:checkbox').is(':checked');

		if (isChecked) {
			$(".inbox-checkbox-triggered").addClass('visible');
			//$("#compose-mail").hide();
		} else {
			$(".inbox-checkbox-triggered").removeClass('visible');
			//$("#compose-mail").show();
		}
	}
        
        //Hide Loading Message
        messenger_hidenotiftop();
    },
    
    /*
     * Order Tracking - Summary
     */
    
    ot_summary: function() {
        if(typeof window['ot_summary'] === "undefined")
        {
            jsLoader(["/js/plugin/datatables/jquery.dataTables-all.min.js",$.trim(document.getElementById('content').getAttribute('data-urljs').toString())]);
        }
        else
        {
            pageSetUp();
            ot_summary();
        }        
    },
    ot_active_charges: function() {
        if(typeof window['ot_active_charges'] === "undefined")
        {
            jsLoader(["/js/plugin/datatables/jquery.dataTables-all.min.js",$.trim(document.getElementById('content').getAttribute('data-urljs').toString())]);
        }
        else
        {
            pageSetUp();
            ot_active_charges();
        }          
    },
    apr_statistics: function() {
        if(typeof window['apr_statistics'] === "undefined")
        {
            jsLoader(["/js/plugin/morris/raphael.2.1.0.min.js","/js/plugin/morris/morris.min.js",$.trim(document.getElementById('content').getAttribute('data-urljs').toString())]);
        }
        else
        {
            pageSetUp();
            apr_statistics();
        }
    },
    salesman_budget: function() {
        if(typeof window['salesman_budget'] === "undefined")
        {
            jsLoader(["/js/plugin/morris/raphael.2.1.0.min.js","/js/plugin/morris/morris.min.js",$.trim(document.getElementById('content').getAttribute('data-urljs').toString())]);
        }
        else
        {
            pageSetUp();
            salesman_budget();
        }
    },
    acp_payment_cheque_issue: function() {
        if(typeof window['acp_payment_cheque_issue'] === "undefined")
        {
            jsLoader(["/js/plugin/context/context.js",$.trim(document.getElementById('content').getAttribute('data-urljs').toString())]);
        }
        else
        {
            pageSetUp();
            acp_payment_cheque_issue();
        }        
    }
}