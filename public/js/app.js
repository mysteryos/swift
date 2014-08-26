	/*
	 * VARIABLES
	 * Description: All Global Vars
	 */
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
	$.enableMobileWidgets = false;


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
        $.pjax.defaults.timeout = 10000;
        $.pjax.defaults.container = "#main";
        
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
	 * BOOTSTRAP SLIDER PLUGIN
	 * Usage:
	 * Dependency: js/plugin/bootstrap-slider
	 */
	if ($.fn.slider) {
		$('.slider').slider();
	}

	/*
	 * SELECT2 PLUGIN
	 * Usage:
	 * Dependency: js/plugin/select2/
	 */
	if ($.fn.select2) {
		$('.select2').each(function() {
			var $this = $(this);
			var width = $this.attr('data-select-width') || '100%';
			//, _showSearchInput = $this.attr('data-select-search') === 'true';
			$this.select2({
				//showSearchInput : _showSearchInput,
				allowClear : true,
				width : width
			})
		})
	}

	/*
	 * MASKING
	 * Dependency: js/plugin/masked-input/
	 */
	if ($.fn.mask) {
		$('[data-mask]').each(function() {

			var $this = $(this);
			var mask = $this.attr('data-mask') || 'error...', mask_placeholder = $this.attr('data-mask-placeholder') || 'X';

			$this.mask(mask, {
				placeholder : mask_placeholder
			});
		})
	}

	/*
	 * Autocomplete
	 * Dependency: js/jqui
	 */
	if ($.fn.autocomplete) {
		$('[data-autocomplete]').each(function() {

			var $this = $(this);
			var availableTags = $this.data('autocomplete') || ["The", "Quick", "Brown", "Fox", "Jumps", "Over", "Three", "Lazy", "Dogs"];

			$this.autocomplete({
				source : availableTags
			});
		})
	}

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
 * INITIALIZE CHARTS
 * Description: Sparklines, PieCharts
 */

function runAllCharts() {
	/*
	 * SPARKLINES
	 * DEPENDENCY: js/plugins/sparkline/jquery.sparkline.min.js
	 * See usage example below...
	 */

	/* Usage:
	 * 		<div class="sparkline-line txt-color-blue" data-fill-color="transparent" data-sparkline-height="26px">
	 *			5,6,7,9,9,5,9,6,5,6,6,7,7,6,7,8,9,7
	 *		</div>
	 */

	if ($.fn.sparkline) {

		$('.sparkline').each(function() {
			var $this = $(this);
			var sparklineType = $this.data('sparkline-type') || 'bar';

			// BAR CHART
			if (sparklineType == 'bar') {

				var barColor = $this.data('sparkline-bar-color') || $this.css('color') || '#0000f0', sparklineHeight = $this.data('sparkline-height') || '26px', sparklineBarWidth = $this.data('sparkline-barwidth') || 5, sparklineBarSpacing = $this.data('sparkline-barspacing') || 2, sparklineNegBarColor = $this.data('sparkline-negbar-color') || '#A90329', sparklineStackedColor = $this.data('sparkline-barstacked-color') || ["#A90329", "#0099c6", "#98AA56", "#da532c", "#4490B1", "#6E9461", "#990099", "#B4CAD3"];

				$this.sparkline('html', {
					type : 'bar',
					barColor : barColor,
					type : sparklineType,
					height : sparklineHeight,
					barWidth : sparklineBarWidth,
					barSpacing : sparklineBarSpacing,
					stackedBarColor : sparklineStackedColor,
					negBarColor : sparklineNegBarColor,
					zeroAxis : 'false'
				});

			}

			//LINE CHART
			if (sparklineType == 'line') {

				var sparklineHeight = $this.data('sparkline-height') || '20px', sparklineWidth = $this.data('sparkline-width') || '90px', thisLineColor = $this.data('sparkline-line-color') || $this.css('color') || '#0000f0', thisLineWidth = $this.data('sparkline-line-width') || 1, thisFill = $this.data('fill-color') || '#c0d0f0', thisSpotColor = $this.data('sparkline-spot-color') || '#f08000', thisMinSpotColor = $this.data('sparkline-minspot-color') || '#ed1c24', thisMaxSpotColor = $this.data('sparkline-maxspot-color') || '#f08000', thishighlightSpotColor = $this.data('sparkline-highlightspot-color') || '#50f050', thisHighlightLineColor = $this.data('sparkline-highlightline-color') || 'f02020', thisSpotRadius = $this.data('sparkline-spotradius') || 1.5;
				thisChartMinYRange = $this.data('sparkline-min-y') || 'undefined', thisChartMaxYRange = $this.data('sparkline-max-y') || 'undefined', thisChartMinXRange = $this.data('sparkline-min-x') || 'undefined', thisChartMaxXRange = $this.data('sparkline-max-x') || 'undefined', thisMinNormValue = $this.data('min-val') || 'undefined', thisMaxNormValue = $this.data('max-val') || 'undefined', thisNormColor = $this.data('norm-color') || '#c0c0c0', thisDrawNormalOnTop = $this.data('draw-normal') || false;

				$this.sparkline('html', {
					type : 'line',
					width : sparklineWidth,
					height : sparklineHeight,
					lineWidth : thisLineWidth,
					lineColor : thisLineColor,
					fillColor : thisFill,
					spotColor : thisSpotColor,
					minSpotColor : thisMinSpotColor,
					maxSpotColor : thisMaxSpotColor,
					highlightSpotColor : thishighlightSpotColor,
					highlightLineColor : thisHighlightLineColor,
					spotRadius : thisSpotRadius,
					chartRangeMin : thisChartMinYRange,
					chartRangeMax : thisChartMaxYRange,
					chartRangeMinX : thisChartMinXRange,
					chartRangeMaxX : thisChartMaxXRange,
					normalRangeMin : thisMinNormValue,
					normalRangeMax : thisMaxNormValue,
					normalRangeColor : thisNormColor,
					drawNormalOnTop : thisDrawNormalOnTop

				});

			}

			//PIE CHART
			if (sparklineType == 'pie') {

				var pieColors = $this.data('sparkline-piecolor') || ["#B4CAD3", "#4490B1", "#98AA56", "#da532c", "#6E9461", "#0099c6", "#990099", "#717D8A"], pieWidthHeight = $this.data('sparkline-piesize') || 90, pieBorderColor = $this.data('border-color') || '#45494C', pieOffset = $this.data('sparkline-offset') || 0;

				$this.sparkline('html', {
					type : 'pie',
					width : pieWidthHeight,
					height : pieWidthHeight,
					tooltipFormat : '<span style="color: {{color}}">&#9679;</span> ({{percent.1}}%)',
					sliceColors : pieColors,
					offset : 0,
					borderWidth : 1,
					offset : pieOffset,
					borderColor : pieBorderColor
				});

			}

			//BOX PLOT
			if (sparklineType == 'box') {

				var thisBoxWidth = $this.data('sparkline-width') || 'auto', thisBoxHeight = $this.data('sparkline-height') || 'auto', thisBoxRaw = $this.data('sparkline-boxraw') || false, thisBoxTarget = $this.data('sparkline-targetval') || 'undefined', thisBoxMin = $this.data('sparkline-min') || 'undefined', thisBoxMax = $this.data('sparkline-max') || 'undefined', thisShowOutlier = $this.data('sparkline-showoutlier') || true, thisIQR = $this.data('sparkline-outlier-iqr') || 1.5, thisBoxSpotRadius = $this.data('sparkline-spotradius') || 1.5, thisBoxLineColor = $this.css('color') || '#000000', thisBoxFillColor = $this.data('fill-color') || '#c0d0f0', thisBoxWhisColor = $this.data('sparkline-whis-color') || '#000000', thisBoxOutlineColor = $this.data('sparkline-outline-color') || '#303030', thisBoxOutlineFill = $this.data('sparkline-outlinefill-color') || '#f0f0f0', thisBoxMedianColor = $this.data('sparkline-outlinemedian-color') || '#f00000', thisBoxTargetColor = $this.data('sparkline-outlinetarget-color') || '#40a020';

				$this.sparkline('html', {
					type : 'box',
					width : thisBoxWidth,
					height : thisBoxHeight,
					raw : thisBoxRaw,
					target : thisBoxTarget,
					minValue : thisBoxMin,
					maxValue : thisBoxMax,
					showOutliers : thisShowOutlier,
					outlierIQR : thisIQR,
					spotRadius : thisBoxSpotRadius,
					boxLineColor : thisBoxLineColor,
					boxFillColor : thisBoxFillColor,
					whiskerColor : thisBoxWhisColor,
					outlierLineColor : thisBoxOutlineColor,
					outlierFillColor : thisBoxOutlineFill,
					medianColor : thisBoxMedianColor,
					targetColor : thisBoxTargetColor

				})

			}

			//BULLET
			if (sparklineType == 'bullet') {

				var thisBulletHeight = $this.data('sparkline-height') || 'auto', thisBulletWidth = $this.data('sparkline-width') || 2, thisBulletColor = $this.data('sparkline-bullet-color') || '#ed1c24', thisBulletPerformanceColor = $this.data('sparkline-performance-color') || '#3030f0', thisBulletRangeColors = $this.data('sparkline-bulletrange-color') || ["#d3dafe", "#a8b6ff", "#7f94ff"]

				$this.sparkline('html', {

					type : 'bullet',
					height : thisBulletHeight,
					targetWidth : thisBulletWidth,
					targetColor : thisBulletColor,
					performanceColor : thisBulletPerformanceColor,
					rangeColors : thisBulletRangeColors

				})

			}

			//DISCRETE
			if (sparklineType == 'discrete') {

				var thisDiscreteHeight = $this.data('sparkline-height') || 26, thisDiscreteWidth = $this.data('sparkline-width') || 50, thisDiscreteLineColor = $this.css('color'), thisDiscreteLineHeight = $this.data('sparkline-line-height') || 5, thisDiscreteThrushold = $this.data('sparkline-threshold') || 'undefined', thisDiscreteThrusholdColor = $this.data('sparkline-threshold-color') || '#ed1c24';

				$this.sparkline('html', {

					type : 'discrete',
					width : thisDiscreteWidth,
					height : thisDiscreteHeight,
					lineColor : thisDiscreteLineColor,
					lineHeight : thisDiscreteLineHeight,
					thresholdValue : thisDiscreteThrushold,
					thresholdColor : thisDiscreteThrusholdColor

				})

			}

			//TRISTATE
			if (sparklineType == 'tristate') {

				var thisTristateHeight = $this.data('sparkline-height') || 26, thisTristatePosBarColor = $this.data('sparkline-posbar-color') || '#60f060', thisTristateNegBarColor = $this.data('sparkline-negbar-color') || '#f04040', thisTristateZeroBarColor = $this.data('sparkline-zerobar-color') || '#909090', thisTristateBarWidth = $this.data('sparkline-barwidth') || 5, thisTristateBarSpacing = $this.data('sparkline-barspacing') || 2, thisZeroAxis = $this.data('sparkline-zeroaxis') || false;

				$this.sparkline('html', {

					type : 'tristate',
					height : thisTristateHeight,
					posBarColor : thisBarColor,
					negBarColor : thisTristateNegBarColor,
					zeroBarColor : thisTristateZeroBarColor,
					barWidth : thisTristateBarWidth,
					barSpacing : thisTristateBarSpacing,
					zeroAxis : thisZeroAxis

				})

			}

			//COMPOSITE: BAR
			if (sparklineType == 'compositebar') {

				var sparklineHeight = $this.data('sparkline-height') || '20px', sparklineWidth = $this.data('sparkline-width') || '100%', sparklineBarWidth = $this.data('sparkline-barwidth') || 3, thisLineWidth = $this.data('sparkline-line-width') || 1, thisLineColor = $this.data('sparkline-color-top') || '#ed1c24', thisBarColor = $this.data('sparkline-color-bottom') || '#333333'

				$this.sparkline($this.data('sparkline-bar-val'), {

					type : 'bar',
					width : sparklineWidth,
					height : sparklineHeight,
					barColor : thisBarColor,
					barWidth : sparklineBarWidth
					//barSpacing: 5

				})

				$this.sparkline($this.data('sparkline-line-val'), {

					width : sparklineWidth,
					height : sparklineHeight,
					lineColor : thisLineColor,
					lineWidth : thisLineWidth,
					composite : true,
					fillColor : false

				})

			}

			//COMPOSITE: LINE
			if (sparklineType == 'compositeline') {

				var sparklineHeight = $this.data('sparkline-height') || '20px', sparklineWidth = $this.data('sparkline-width') || '90px', sparklineValue = $this.data('sparkline-bar-val'), sparklineValueSpots1 = $this.data('sparkline-bar-val-spots-top') || null, sparklineValueSpots2 = $this.data('sparkline-bar-val-spots-bottom') || null, thisLineWidth1 = $this.data('sparkline-line-width-top') || 1, thisLineWidth2 = $this.data('sparkline-line-width-bottom') || 1, thisLineColor1 = $this.data('sparkline-color-top') || '#333333', thisLineColor2 = $this.data('sparkline-color-bottom') || '#ed1c24', thisSpotRadius1 = $this.data('sparkline-spotradius-top') || 1.5, thisSpotRadius2 = $this.data('sparkline-spotradius-bottom') || thisSpotRadius1, thisSpotColor = $this.data('sparkline-spot-color') || '#f08000', thisMinSpotColor1 = $this.data('sparkline-minspot-color-top') || '#ed1c24', thisMaxSpotColor1 = $this.data('sparkline-maxspot-color-top') || '#f08000', thisMinSpotColor2 = $this.data('sparkline-minspot-color-bottom') || thisMinSpotColor1, thisMaxSpotColor2 = $this.data('sparkline-maxspot-color-bottom') || thisMaxSpotColor1, thishighlightSpotColor1 = $this.data('sparkline-highlightspot-color-top') || '#50f050', thisHighlightLineColor1 = $this.data('sparkline-highlightline-color-top') || '#f02020', thishighlightSpotColor2 = $this.data('sparkline-highlightspot-color-bottom') || thishighlightSpotColor1, thisHighlightLineColor2 = $this.data('sparkline-highlightline-color-bottom') || thisHighlightLineColor1, thisFillColor1 = $this.data('sparkline-fillcolor-top') || 'transparent', thisFillColor2 = $this.data('sparkline-fillcolor-bottom') || 'transparent';

				$this.sparkline(sparklineValue, {

					type : 'line',
					spotRadius : thisSpotRadius1,

					spotColor : thisSpotColor,
					minSpotColor : thisMinSpotColor1,
					maxSpotColor : thisMaxSpotColor1,
					highlightSpotColor : thishighlightSpotColor1,
					highlightLineColor : thisHighlightLineColor1,

					valueSpots : sparklineValueSpots1,

					lineWidth : thisLineWidth1,
					width : sparklineWidth,
					height : sparklineHeight,
					lineColor : thisLineColor1,
					fillColor : thisFillColor1

				})

				$this.sparkline($this.data('sparkline-line-val'), {

					type : 'line',
					spotRadius : thisSpotRadius2,

					spotColor : thisSpotColor,
					minSpotColor : thisMinSpotColor2,
					maxSpotColor : thisMaxSpotColor2,
					highlightSpotColor : thishighlightSpotColor2,
					highlightLineColor : thisHighlightLineColor2,

					valueSpots : sparklineValueSpots2,

					lineWidth : thisLineWidth2,
					width : sparklineWidth,
					height : sparklineHeight,
					lineColor : thisLineColor2,
					composite : true,
					fillColor : thisFillColor2

				})

			}

		});

	}// end if

	/*
	 * EASY PIE CHARTS
	 * DEPENDENCY: js/plugins/easy-pie-chart/jquery.easy-pie-chart.min.js
	 * Usage: <div class="easy-pie-chart txt-color-orangeDark" data-pie-percent="33" data-pie-size="72" data-size="72">
	 *			<span class="percent percent-sign">35</span>
	 * 	  	  </div>
	 */

	if ($.fn.easyPieChart) {

		$('.easy-pie-chart').each(function() {
			var $this = $(this);
			var barColor = $this.css('color') || $this.data('pie-color'), trackColor = $this.data('pie-track-color') || '#eeeeee', size = parseInt($this.data('pie-size')) || 25;
			$this.easyPieChart({
				barColor : barColor,
				trackColor : trackColor,
				scaleColor : false,
				lineCap : 'butt',
				lineWidth : parseInt(size / 8.5),
				animate : 1500,
				rotate : -90,
				size : size,
				onStep : function(value) {
					this.$el.find('span').text(~~value);
				}
			});
		});

	} // end if

}

/* ~ END: INITIALIZE CHARTS */

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

// UPDATE BREADCRUMB
function drawBreadCrumb() {
	var nav_elems = $('nav li.active > a'), count = nav_elems.length;
	
	//console.log("breadcrumb")
	$.bread_crumb.empty();
	$.bread_crumb.append($("<li>Home</li>"));
	nav_elems.each(function() {
		$.bread_crumb.append($("<li></li>").html($.trim($(this).clone().children(".badge").remove().end().text())));
		// update title when breadcrumb is finished...
		if (!--count) document.title = $.bread_crumb.find("li:last-child").text();
	});

}

//Init Dropzone Notif box & Events

function initDropzone() {
    Dropzone.options.theAwesomeDropZone = {
      init: function() {
        
      }
    };    
}

//PJAX Setup

/*
 * Bind links with pjax class
 */
$.document_.pjax('a.pjax', '#main');

/*
 * After HTML Replace
 */
$.document_.on('pjax:success, pjax:end',function(){
    //Execute Javascript
    if(typeof $('#content').attr('data-js') !== "undefined")
    {
        main[$('#content').attr('data-js').toString()]();
    }
    else
    {
        messenger_hidenotiftop();
    }
});

$.document_.on('pjax:beforeSend',function(){
    messenger_notiftop('Loading..','info',0);
});

/* ~ END: APP AJAX REQUEST SETUP */

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
	
		// activate inline charts
		runAllCharts();
	
		// setup widgets
		setup_widgets_desktop();
	
		//setup nav height (dynamic)
		nav_page_height();
	
		// run form elements
		runAllForms();

	} else {
		
		// is mobile
		
		// activate popovers
		$("[rel=popover]").popover();
	
		// activate popovers with hover states
		$("[rel=popover-hover]").popover({
			trigger : "hover"
		});
	
		// activate inline charts
		runAllCharts();
	
		// setup widgets
		setup_widgets_mobile();
	
		//setup nav height (dynamic)
		nav_page_height();
	
		// run form elements
		runAllForms();
		
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
			// console.log("Ajax call for activity")
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
        
        //DropZone
        initDropzone();

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


var main = {
    /*
     * Dashboard Index Js
     */
    dashboard: function () {
        //Global Functions

        pageSetUp();

        /*
         * PAGE RELATED SCRIPTS
         */
        $.maindiv.find(".js-status-update a").click(function() {
                var selText = $(this).text();
                var $this = $(this);
                $this.parents('.btn-group').find('.dropdown-toggle').html(selText + ' <span class="caret"></span>');
                $this.parents('.dropdown-menu').find('li').removeClass('active');
                $this.parent().addClass('active');
        });

        /*
        * TODO: add a way to add more todo's to list
        */

        // initialize sortable
        $.maindiv.find("#sortable1, #sortable2").sortable({
                handle : '.handle',
                connectWith : ".todo",
                update : countTasks
        }).disableSelection();

        // check and uncheck
        $.maindiv.find('.todo .checkbox > input[type="checkbox"]').click(function() {
                var $this = $(this).parent().parent().parent();

                if ($(this).prop('checked')) {
                        $this.addClass("complete");

                        // remove this if you want to undo a check list once checked
                        //$(this).attr("disabled", true);
                        $(this).parent().hide();

                        // once clicked - add class, copy to memory then remove and add to sortable3
                        $this.slideUp(500, function() {
                                $this.clone().prependTo("#sortable3").effect("highlight", {}, 800);
                                $this.remove();
                                countTasks();
                        });
                } else {
                        // insert undo code here...
                }

        })
        // count tasks
        function countTasks() {

                $.maindiv.find('.todo-group-title').each(function() {
                        var $this = $(this);
                        $this.find(".num-of-tasks").text($this.next().find("li").size());
                });

        }

        /*
        * RUN PAGE GRAPHS
        */

        /* TAB 1: UPDATING CHART */
        // For the demo we use generated data, but normally it would be coming from the server

        var data = [], totalPoints = 200, $UpdatingChartColors = $("#updating-chart").css('color');

        function getRandomData() {
                if (data.length > 0)
                        data = data.slice(1);

                // do a random walk
                while (data.length < totalPoints) {
                        var prev = data.length > 0 ? data[data.length - 1] : 50;
                        var y = prev + Math.random() * 10 - 5;
                        if (y < 0)
                                y = 0;
                        if (y > 100)
                                y = 100;
                        data.push(y);
                }

                // zip the generated y values with the x values
                var res = [];
                for (var i = 0; i < data.length; ++i)
                        res.push([i, data[i]])
                return res;
        }

        // setup control widget
        var updateInterval = 1500;
        $("#updating-chart").val(updateInterval).change(function() {

                var v = $(this).val();
                if (v && !isNaN(+v)) {
                        updateInterval = +v;
                        $(this).val("" + updateInterval);
                }

        });

        // setup plot
        var options = {
                yaxis : {
                        min : 0,
                        max : 100
                },
                xaxis : {
                        min : 0,
                        max : 100
                },
                colors : [$UpdatingChartColors],
                series : {
                        lines : {
                                lineWidth : 1,
                                fill : true,
                                fillColor : {
                                        colors : [{
                                                opacity : 0.4
                                        }, {
                                                opacity : 0
                                        }]
                                },
                                steps : false

                        }
                }
        };

        var plot = $.plot($("#updating-chart"), [getRandomData()], options);

        /* live switch */
        $('input[type="checkbox"]#start_interval').click(function() {
                if ($(this).prop('checked')) {
                        $on = true;
                        updateInterval = 1500;
                        update();
                } else {
                        clearInterval(updateInterval);
                        $on = false;
                }
        });

        function update() {
                if ($on == true) {
                        plot.setData([getRandomData()]);
                        plot.draw();
                        setTimeout(update, updateInterval);

                } else {
                        clearInterval(updateInterval)
                }

        }

        var $on = false;

        /*end updating chart*/

        /* TAB 2: Social Network  */

        // jQuery Flot Chart
        var twitter = [[1, 27], [2, 34], [3, 51], [4, 48], [5, 55], [6, 65], [7, 61], [8, 70], [9, 65], [10, 75], [11, 57], [12, 59], [13, 62]], facebook = [[1, 25], [2, 31], [3, 45], [4, 37], [5, 38], [6, 40], [7, 47], [8, 55], [9, 43], [10, 50], [11, 47], [12, 39], [13, 47]], data = [{
                label : "Twitter",
                data : twitter,
                lines : {
                        show : true,
                        lineWidth : 1,
                        fill : true,
                        fillColor : {
                                colors : [{
                                        opacity : 0.1
                                }, {
                                        opacity : 0.13
                                }]
                        }
                },
                points : {
                        show : true
                }
        }, {
                label : "Facebook",
                data : facebook,
                lines : {
                        show : true,
                        lineWidth : 1,
                        fill : true,
                        fillColor : {
                                colors : [{
                                        opacity : 0.1
                                }, {
                                        opacity : 0.13
                                }]
                        }
                },
                points : {
                        show : true
                }
        }];

        var options = {
                grid : {
                        hoverable : true
                },
                colors : ["#568A89", "#3276B1"],
                tooltip : true,
                tooltipOpts : {
                        //content : "Value <b>$x</b> Value <span>$y</span>",
                        defaultTheme : false
                },
                xaxis : {
                        ticks : [[1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"], [13, "JAN+1"]]
                },
                yaxes : {

                }
        };

        var plot3 = $.plot($("#statsChart"), data, options);

        // END TAB 2

        // TAB THREE GRAPH //
        /* TAB 3: Revenew  */

        var trgt = [[1354586000000, 153], [1364587000000, 658], [1374588000000, 198], [1384589000000, 663], [1394590000000, 801], [1404591000000, 1080], [1414592000000, 353], [1424593000000, 749], [1434594000000, 523], [1444595000000, 258], [1454596000000, 688], [1464597000000, 364]], prft = [[1354586000000, 53], [1364587000000, 65], [1374588000000, 98], [1384589000000, 83], [1394590000000, 980], [1404591000000, 808], [1414592000000, 720], [1424593000000, 674], [1434594000000, 23], [1444595000000, 79], [1454596000000, 88], [1464597000000, 36]], sgnups = [[1354586000000, 647], [1364587000000, 435], [1374588000000, 784], [1384589000000, 346], [1394590000000, 487], [1404591000000, 463], [1414592000000, 479], [1424593000000, 236], [1434594000000, 843], [1444595000000, 657], [1454596000000, 241], [1464597000000, 341]], toggles = $("#rev-toggles"), target = $("#flotcontainer");

        var data = [{
                label : "Target Profit",
                data : trgt,
                bars : {
                        show : true,
                        align : "center",
                        barWidth : 30 * 30 * 60 * 1000 * 80
                }
        }, {
                label : "Actual Profit",
                data : prft,
                color : '#3276B1',
                lines : {
                        show : true,
                        lineWidth : 3
                },
                points : {
                        show : true
                }
        }, {
                label : "Actual Signups",
                data : sgnups,
                color : '#71843F',
                lines : {
                        show : true,
                        lineWidth : 1
                },
                points : {
                        show : true
                }
        }]

        var options = {
                grid : {
                        hoverable : true
                },
                tooltip : true,
                tooltipOpts : {
                        //content: '%x - %y',
                        //dateFormat: '%b %y',
                        defaultTheme : false
                },
                xaxis : {
                        mode : "time"
                },
                yaxes : {
                        tickFormatter : function(val, axis) {
                                return "$" + val;
                        },
                        max : 1200
                }

        };

        plot2 = null;

        function plotNow() {
                var d = [];
                toggles.find(':checkbox').each(function() {
                        if ($(this).is(':checked')) {
                                d.push(data[$(this).attr("name").substr(4, 1)]);
                        }
                });
                if (d.length > 0) {
                        if (plot2) {
                                plot2.setData(d);
                                plot2.draw();
                        } else {
                                plot2 = $.plot(target, d, options);
                        }
                }

        };

        toggles.find(':checkbox').on('change', function() {
                plotNow();
        });
        plotNow();

        /*
         * VECTOR MAP
         */

        data_array = {
                "US" : 4977,
                "AU" : 4873,
                "IN" : 3671,
                "BR" : 2476,
                "TR" : 1476,
                "CN" : 146,
                "CA" : 134,
                "BD" : 100
        };

        $('#vector-map').vectorMap({
                map : 'world_mill_en',
                backgroundColor : '#fff',
                regionStyle : {
                        initial : {
                                fill : '#c4c4c4'
                        },
                        hover : {
                                "fill-opacity" : 1
                        }
                },
                series : {
                        regions : [{
                                values : data_array,
                                scale : ['#85a8b6', '#4d7686'],
                                normalizeFunction : 'polynomial'
                        }]
                },
                onRegionLabelShow : function(e, el, code) {
                        if ( typeof data_array[code] == 'undefined') {
                                e.preventDefault();
                        } else {
                                var countrylbl = data_array[code];
                                el.html(el.html() + ': ' + countrylbl + ' visits');
                        }
                }
        });

        /*
         * FULL CALENDAR JS
         */

        if ($("#calendar").length) {
                var date = new Date();
                var d = date.getDate();
                var m = date.getMonth();
                var y = date.getFullYear();

                var calendar = $('#calendar').fullCalendar({

                        editable : true,
                        draggable : true,
                        selectable : false,
                        selectHelper : true,
                        unselectAuto : false,
                        disableResizing : false,

                        header : {
                                left : 'title', //,today
                                center : 'prev, next, today',
                                right : 'month, agendaWeek, agenDay' //month, agendaDay,
                        },

                        select : function(start, end, allDay) {
                                var title = prompt('Event Title:');
                                if (title) {
                                        calendar.fullCalendar('renderEvent', {
                                                title : title,
                                                start : start,
                                                end : end,
                                                allDay : allDay
                                        }, true // make the event "stick"
                                        );
                                }
                                calendar.fullCalendar('unselect');
                        },

                        events : [{
                                title : 'All Day Event',
                                start : new Date(y, m, 1),
                                description : 'long description',
                                className : ["event", "bg-color-greenLight"],
                                icon : 'fa-check'
                        }, {
                                title : 'Long Event',
                                start : new Date(y, m, d - 5),
                                end : new Date(y, m, d - 2),
                                className : ["event", "bg-color-red"],
                                icon : 'fa-lock'
                        }, {
                                id : 999,
                                title : 'Repeating Event',
                                start : new Date(y, m, d - 3, 16, 0),
                                allDay : false,
                                className : ["event", "bg-color-blue"],
                                icon : 'fa-clock-o'
                        }, {
                                id : 999,
                                title : 'Repeating Event',
                                start : new Date(y, m, d + 4, 16, 0),
                                allDay : false,
                                className : ["event", "bg-color-blue"],
                                icon : 'fa-clock-o'
                        }, {
                                title : 'Meeting',
                                start : new Date(y, m, d, 10, 30),
                                allDay : false,
                                className : ["event", "bg-color-darken"]
                        }, {
                                title : 'Lunch',
                                start : new Date(y, m, d, 12, 0),
                                end : new Date(y, m, d, 14, 0),
                                allDay : false,
                                className : ["event", "bg-color-darken"]
                        }, {
                                title : 'Birthday Party',
                                start : new Date(y, m, d + 1, 19, 0),
                                end : new Date(y, m, d + 1, 22, 30),
                                allDay : false,
                                className : ["event", "bg-color-darken"]
                        }, {
                                title : 'Smartadmin Open Day',
                                start : new Date(y, m, 28),
                                end : new Date(y, m, 29),
                                className : ["event", "bg-color-darken"]
                        }],

                        eventRender : function(event, element, icon) {
                                if (!event.description == "") {
                                        element.find('.fc-event-title').append("<br/><span class='ultra-light'>" + event.description + "</span>");
                                }
                                if (!event.icon == "") {
                                        element.find('.fc-event-title').append("<i class='air air-top-right fa " + event.icon + " '></i>");
                                }
                        }
                });

        };

        /* hide default buttons */
        $('.fc-header-right, .fc-header-center').hide();

        // calendar prev
        $('#calendar-buttons #btn-prev').click(function() {
                $('.fc-button-prev').click();
                return false;
        });

        // calendar next
        $('#calendar-buttons #btn-next').click(function() {
                $('.fc-button-next').click();
                return false;
        });

        // calendar today
        $('#calendar-buttons #btn-today').click(function() {
                $('.fc-button-today').click();
                return false;
        });

        // calendar month
        $('#mt').click(function() {
                $('#calendar').fullCalendar('changeView', 'month');
        });

        // calendar agenda week
        $('#ag').click(function() {
                $('#calendar').fullCalendar('changeView', 'agendaWeek');
        });

        // calendar agenda day
        $('#td').click(function() {
                $('#calendar').fullCalendar('changeView', 'agendaDay');
        });

        /*
         * CHAT
         */

        $.filter_input = $('#filter-chat-list');
        $.chat_users_container = $('#chat-container > .chat-list-body')
        $.chat_users = $('#chat-users')
        $.chat_list_btn = $('#chat-container > .chat-list-open-close');
        $.chat_body = $('#chat-body');

        /*
        * LIST FILTER (CHAT)
        */

        // custom css expression for a case-insensitive contains()
        jQuery.expr[':'].Contains = function(a, i, m) {
                return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
        };

        function listFilter(list) {// header is any element, list is an unordered list
                // create and add the filter form to the header

                $.filter_input.change(function() {
                        var filter = $(this).val();
                        if (filter) {
                                // this finds all links in a list that contain the input,
                                // and hide the ones not containing the input while showing the ones that do
                                $.chat_users.find("a:not(:Contains(" + filter + "))").parent().slideUp();
                                $.chat_users.find("a:Contains(" + filter + ")").parent().slideDown();
                        } else {
                                $.chat_users.find("li").slideDown();
                        }
                        return false;
                }).keyup(function() {
                        // fire the above change event after every letter
                        $(this).change();

                });

        }

        // on dom ready
        listFilter($.chat_users);

        // open chat list
        $.chat_list_btn.click(function() {
                $(this).parent('#chat-container').toggleClass('open');
        })

        $.chat_body.animate({
                scrollTop : $.chat_body[0].scrollHeight
        }, 500);
        
        //Hide Loading Message
        messenger_hidenotiftop();
    },
    
    /*
     * Inbox Index JS
     */
    
    inbox: function () {
        // DO NOT REMOVE : GLOBAL FUNCTIONS!
        pageSetUp();

        // PAGE RELATED SCRIPTS
        
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
     * Order Tracking : Create JS
     */
    ot_create: function() {
        // DO NOT REMOVE : GLOBAL FUNCTIONS!
	pageSetUp();
	
	
	// PAGE RELATED SCRIPTS

	// Load form valisation dependency 
	// Load form valisation dependency 
        var myqueue = new createjs.LoadQueue(false);
        myqueue.on("complete",runFormValidation,this);
        myqueue.loadManifest(["/js/plugin/jquery-form/jquery-form.min.js"]);        
	
	// Registration validation script
	function runFormValidation() {
		
                var $orderTrackingCreateForm = $('#order-tracking-create-form').validate({
                    rules : {
                        name: {
                                required: true
                        }
                    },
                    messages: {
                        name: {
                            required: 'Please enter a name to identify this order'
                        }
                    },
                    
                    // Ajax form submition
                    submitHandler : function(form) {
                            var savemsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                                            message: 'Saving order tracking form',
                                            type: 'info',
                                            id: 'notif-top'
                                          });
                            $('#save-draft').attr('disabled','disabled').addClass('disable');
                            $(form).ajaxSubmit({
                                    dataType: 'json',
                                    success : function(data) {
                                        savemsg.update({
                                            type: 'success',
                                            message: 'Save Success!',
                                        });
                                        $.pjax({
                                            container: '#main',
                                            url: data.url,
                                            beforeReplace: function()
                                            {
                                                savemsg.hide();
                                            }
                                        });
                                    },
                                    error: function (data) {
                                        $('#save-draft').removeAttr('disabled').removeClass('disable');
                                        savemsg.update({
                                            type: 'error',
                                            message: 'Save Failed! Please retry.',
                                            hideAfter: 5,
                                        });
                                    }
                            });

                            return false;
                    }
                });

		// START AND FINISH DATE
		$('#startdate').datepicker({
			dateFormat : 'dd.mm.yy',
			prevText : '<i class="fa fa-chevron-left"></i>',
			nextText : '<i class="fa fa-chevron-right"></i>',
			onSelect : function(selectedDate) {
				$('#finishdate').datepicker('option', 'minDate', selectedDate);
			}
		});
		
		$('#finishdate').datepicker({
			dateFormat : 'dd.mm.yy',
			prevText : '<i class="fa fa-chevron-left"></i>',
			nextText : '<i class="fa fa-chevron-right"></i>',
			onSelect : function(selectedDate) {
				$('#startdate').datepicker('option', 'maxDate', selectedDate);
			}
		});

//                $('#freightcompanysearch').select2({
//                    placeholder: "Select a freight company",
//                    minimumInputLength: 2,
//                    allowClear: true,
//                    ajax: {
//                        url:'/ajaxsearch/freightcompany',
//                        dataType: "json",
//                        quietMillis: 500,
//                        data: function (term, page) {
//                            return {
//                                term: term,
//                                limit: 5,
//                                page: page
//                            };
//                        },
//                        results: function (data, page){
//                            var more = (page * 5) < data.total
//                            if(data.total > 0)
//                            {
//                                var found;
//                                found = $.map(data.freightcompany, function (item) {
//                                    return {
//                                        id: item.id,
//                                        name: item.name,
//                                        text: item.name,
//                                    }
//                                 });
//                                return {results: found, more:more};
//                            }
//                            else
//                            {
//                                return {results: ''};
//                            }
//                        }
//                    }
//                });
//                
//                $('#suppliersearch').select2({
//                    placeholder: "Select a supplier",
//                    minimumInputLength: 2,
//                    allowClear: true,
//                    ajax: {
//                        url:'/ajaxsearch/searchsupplier',
//                        dataType: "json",
//                        quietMillis: 500,
//                        data: function (term, page) {
//                            return {
//                                term: term,
//                                limit: 5,
//                                page: page
//                            };
//                        },
//                        results: function (data, page){
//                            var more = (page * 5) < data.total
//                            if(data.total > 0)
//                            {
//                                var found;
//                                found = $.map(data.suppliers, function (item) {
//                                    return {
//                                        id: item.Supplier_Code,
//                                        name: item.Supplier_Name,
//                                        text: item.Supplier_Name+" (Code:"+item.Supplier_Code+")",
//                                    }
//                                 });
//                                return {results: found, more:more};
//                            }
//                            else
//                            {
//                                return {results: ''};
//                            }
//                        }
//                    }
//                });
            messenger_hidenotiftop();
	};         
    },
    ot_view: function() {
        // DO NOT REMOVE : GLOBAL FUNCTIONS!
	pageSetUp();
	// PAGE RELATED SCRIPTS
        messenger_notiftop('Loading..','info',0);
	// Load form valisation dependency 
        var myqueue = new createjs.LoadQueue(false);
        myqueue.on("complete",runFormNonEditable,this);
        myqueue.loadManifest(["/js/plugin/x-editable/x-editable.js"]);
        myqueue.on("error",function(e){
            messenger_notiftop('Failed to load page. <a class="pjax" href="'+document.URL+'">Click here to reload</a>','error',0)
        });

        function runFormNonEditable()
        {
            //General Info
            $('.editable:not(.dummy)').editable({
                disabled: true
            });
            
            //File View
            $('#ot-docs').on('click','a.file-view',function(e){
               e.preventDefault();
               vex.open({
                   className: 'vex-theme-default vex-file-viewer',
                   content:'<div class="row"><div class="col-xs-12 text-align-center">'+$(this).html()+'</div></div><iframe src="http://docs.google.com/viewer?url='+encodeURIComponent($(this).attr('href'))+'&embedded=true" class="file-viewer"></iframe>',
               }).height($(window).height()).width($(window).width()*0.9);
               
               return false;
            });
            
            $('#upload-preview #template').hide();
            $('#upload-preview').find('button.btn.delete').hide();
            
            
        }
        
        //Hide Loading Message
        messenger_hidenotiftop();        
    },
    
    ot_edit: function() {
        // DO NOT REMOVE : GLOBAL FUNCTIONS!
	pageSetUp();
	// PAGE RELATED SCRIPTS
        messenger_notiftop('Loading..','info',0);
	// Load form valisation dependency 
        var myqueue = new createjs.LoadQueue(false);
        myqueue.on("complete",runFormEditable,this);
        myqueue.loadManifest(["/js/plugin/x-editable/x-editable.js"]);
        myqueue.on("error",function(e){
            messenger_notiftop('Failed to load page. <a class="pjax" href="'+document.URL+'">Click here to reload</a>','error',0)
        });
        
        //Ribbon Buttons
        $('a.btn-ribbon-cancel').on('click',function(e){
            e.preventDefault();            
            var $this = $(this);
            $.SmartMessageBox({
                    title : "<i class='fa fa-times txt-color-red'></i> <span class='txt-color-red'><strong>Are you sure you wish to cancel this order process?</strong></span> ?",
                    content : "The form will be locked from editing after cancellation",
                    buttons : '[No][Yes]'

            }, function(ButtonPressed) {
                    if (ButtonPressed == "Yes") {
                        Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
                            id: 'notif-top',
                            errorMessage: 'Error cancelling order process',
                            successMessage: 'Order process has been cancelled',
                            progressMessage: 'Please Wait...',
                            action: $.ajax,
                        },
                        {
                            type:'POST',
                            url: $this.attr('href'),
                            success:function()
                            {
                                window.setTimeout(function(){
                                    $.pjax({
                                       href: document.URL,
                                       container: '#main'
                                    });
                                },'2000');
                            },
                            error:function(xhr, status, error)
                            {
                                return xhr.responseText;
                            }
                        });                        
                    }

            });
            return false;
        });
        
        /*
         * Run function after script load
         */
        function runFormEditable()
        {
            //Turn on inline Mode
            $.fn.editable.defaults.mode = 'inline';
            $.fn.editable.defaults.ajaxOptions = {type: "put"};
            
            //General Info
            $('.editable:not(.dummy)').editable();
            
            //Customs
            $('.customs-editable,.freight-editable,.purchaseorder-editable').on('save',function(e,params){
                //First time save, set primary key
                if($(this).attr('data-pk') == "0")
                {
                    $thisfieldset = $(this).parents('fieldset');
                    $thisfieldset.find('a.editable').editable('option', 'pk', params.response);
                    $thisfieldset.find('a.editable').attr('data-pk',params.response);
                }
            });
            
            /*
             * Add New
             */
            $('.btn-add-new').on('click',function(){
                $this = $(this);
                $thiswidget = $this.parents('.jarviswidget');
                $clone = $thiswidget.find('fieldset.dummy').clone();
                if($clone[0].length !== 0)
                {
                    $clone.removeClass('hide dummy');
                    $clone.find('.editable').removeClass('dummy');
                    $clone.find('.editable').editable().on('save',function(e,params){
                        //First time save, set primary key
                        if($(this).attr('data-pk') == "0")
                        {
                            $thisfieldset = $(this).parents('fieldset');
                            $thisfieldset.find('a.editable').editable('option', 'pk', params.response);
                            $thisfieldset.find('a.editable').attr('data-pk',params.response);
                        }
                    });
                    $thiswidget.find('form').append($clone);
                }
                else
                {
                    messenger_notiftop('Error: Cannot add new record','error');
                }
            });
            
            $('.jarviswidget').on('click','fieldset.multi .btn-delete',function(){
                $this = $(this);
                if(confirm('Are you sure you wish to delete this record?'))
                {
                    var $thisname = $this.parents('fieldset.multi').attr('data-name').ucfirst();
                    var $thiseditable = $this.parents('fieldset.multi').find('a.editable:first');
                    if($thiseditable.attr('data-pk')=="0")
                    {
                        $this.parents('fieldset.multi').slideUp('500',function(){
                           $(this).remove(); 
                        });
                        messenger_notiftop($thisname+' entry has been deleted','success');
                    }
                    else
                    {
                        Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
                            id: 'notif-top',
                            errorMessage: 'Error removing '+$thisname+' entry',
                            successMessage: $thisname+' entry has been deleted',
                            progressMessage: 'Please Wait...',
                            action: $.ajax,
                        },
                        {
                            type:'DELETE',
                            url: $this.attr('href'),
                            data: {pk:$thiseditable.attr('data-pk')},
                            success:function()
                            {
                                $this.parents('fieldset.multi').slideUp('500',function(){
                                   $(this).remove(); 
                                });
                            },
                            error:function(xhr, status, error)
                            {
                                return xhr.responseText;
                            }
                        });
                    }
                }
                return false;
            });
            
            /*
             * Dropzone
             */
            
            // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
            var previewNode = document.querySelector("#template");
            var previewTemplate = previewNode.parentNode.innerHTML;
            previewNode.style.display = "none";
            var removeFileEvent;
            var theAwesomeDropZone= new Dropzone(document.getElementById('content'),{
                url:'/order-tracking/upload/'+$('#id').val(),
                clickable: '#btn-upload',
                previewsContainer: "#upload-preview",
                previewTemplate: previewTemplate,
                autoQueue: true,
                createImageThumbnails : false,
                addedfile: function(file) {
                    var node, removeLink, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2, _results;
                    if (this.element === this.previewsContainer) {
                      this.element.classList.add("dz-started");
                    }
                    if (this.previewsContainer) {
                      file.previewElement = Dropzone.createElement(this.options.previewTemplate.trim());
                      file.previewTemplate = file.previewElement;
                      this.previewsContainer.appendChild(file.previewElement);
                      _ref = file.previewElement.querySelectorAll("[data-dz-name]");
                      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                        node = _ref[_i];
                        node.textContent = file.name;
                      }
                      _ref1 = file.previewElement.querySelectorAll("[data-dz-size]");
                      for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                        node = _ref1[_j];
                        node.innerHTML = this.filesize(file.size);
                      }
                      if (this.options.addRemoveLinks) {
                        file._removeLink = Dropzone.createElement("<a class=\"dz-remove\" href=\"javascript:undefined;\" data-dz-remove>" + this.options.dictRemoveFile + "</a>");
                        file.previewElement.appendChild(file._removeLink);
                      }
                      removeFileEvent = (function(_this) {
                        return function(e) {
                          e.preventDefault();
                          e.stopPropagation();
                          if (file.status === Dropzone.UPLOADING) {
                            return Dropzone.confirm(_this.options.dictCancelUploadConfirmation+file.name+" ?", function() {
                              return _this.removeFile(file);
                            });
                          } else {
                            if (_this.options.dictRemoveFileConfirmation) {
                              return Dropzone.confirm(_this.options.dictRemoveFileConfirmation+file.name+" ?", function() {
                                    var deletemsg = Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
                                        type: 'info',
                                        message: 'Deleting file '+file.name
                                    });
                                    if($(file.previewElement).hasClass('dz-error'))
                                    {
                                        _this.removeFile(file);
                                    }
                                    else
                                    {
                                        $.ajax({    
                                            type:'DELETE',
                                            url: '/order-tracking/upload/'+$(file.previewElement).attr('data-id'),
                                            success:function()
                                            {
                                                deletemsg.update({
                                                    type: 'success',
                                                    message: file.name+' has been deleted',
                                                    hideAfter: 3,
                                                    showCloseButton: true
                                                });
                                                _this.removeFile(file);
                                            },
                                            error:function(xhr, status, error)
                                            {
                                                deletemsg.update({
                                                    type: 'error',
                                                    message: xhr.responseText,
                                                    showCloseButton: true
                                                });
                                            }                                        
                                        });                                        
                                    }
                                    
                                    return true;
                              });
                            } else {
                              return _this.removeFile(file);
                            }
                          }
                        };
                      })(this);
                      _ref2 = file.previewElement.querySelectorAll("[data-dz-remove]");
                      _results = [];
                      for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
                        removeLink = _ref2[_k];
                        _results.push(removeLink.addEventListener("click", removeFileEvent));
                      }
                      return _results;
                    }
                },
                init: function() {
                    $('#upload-preview .btn.delete').on('click',function(){
                        var $thisParent = $(this).closest('.row.dz-success');
                        if(confirm('Are you sure you want to delete this file: '+$thisParent.attr('data-name')+' ?'))
                        {
                            var deletemsg = Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
                                type: 'info',
                                message: 'Deleting file '+$thisParent.attr('data-name')
                            });

                            $.ajax({
                                type:'DELETE',
                                url: '/order-tracking/upload/'+$thisParent.attr('data-id'),
                                success:function()
                                {
                                    deletemsg.update({
                                        type: 'success',
                                        message: $thisParent.attr('data-name')+' has been deleted',
                                        hideAfter: 3,
                                        showCloseButton: true
                                    });
                                    $thisParent.remove();
                                },
                                error:function(xhr, status, error)
                                {
                                    deletemsg.update({
                                        type: 'error',
                                        message: xhr.responseText,
                                        showCloseButton: true
                                    });
                                }                                        
                            });
                        }
                        return false;
                    });
                    
                    //Init Tags
                    
                }
            });

            theAwesomeDropZone.on("sending", function(file,xhr,formdata) {
                    uploadmsg = Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
                    message: 'Uploading "'+file.name+'" <div class="progress progress-sm progress-striped active"><div aria-valuetransitiongoal="25" class="progress-bar bg-color-blue" id="upload-progress" style="width: 0%;" aria-valuenow="25"></div> </div>',
                    hideAfter: 0,
                    showCloseButton: false,
                    type: 'info',
                    id: 'uploadmsg'
                });
                
                switch(file.type)
                {
                    case "image/jpeg":
                    case "image/png":
                    case "image/bmp":
                    case "image/jpg":
                        var icon = '<i class="fa fa-file-image-o"></i>';
                        break;
                    case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                    case "application/vnd.ms-excel":
                        var icon = '<i class="fa fa-file-excel-o"></i>';
                        break;
                    case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                    case "application/msword":
                        var icon = '<i class="fa fa-file-word-o"></i>';
                        break;
                    case "application/pdf":
                        var icon = '<i class="fa fa-file-pdf-o"></i>';
                        break;                        
                    default:
                        var icon = '<i class="fa fa-file-o"></i>';
                        break;
                }
                
                $(file.previewElement).find('span.name').prepend(icon);
                $(file.previewElement).find('div.progress').removeClass('hide');
                return file;
            });

            theAwesomeDropZone.on("success", function (file,response){
                Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
                    showCloseButton: true,
                    type: 'success',
                    message: "Upload Complete: "+file.name,
                    hideAfter: 3
                });
                var res = $.parseJSON(response);
                if(res.success)
                {   
                    //Add File preview anchor
                    $(file.previewElement).find('span.name').wrapInner("<a class='file-view' rel='tooltip' data-original-title='Last update: "+res.updated_on+" &#013; Updated By: "+res.updated_by+"' data-placement='bottom' href='"+res.url+"'/>");
                    //Set Doc Id
                    $(file.previewElement).attr('data-id',res.id);
                    $(file.previewElement).find("[rel=tooltip]").tooltip();
                    //Set Tag Editable
                    var $editable = $(file.previewElement).find('a.editable');
                    $editable.html('');
                    $editable.attr('data-pk',res.id);
                    $editable.removeAttr('data-value');
                    $editable.editable();
                    $editable.removeClass('dummy hide');

                }
                //Hide Progress Bar
                $(file.previewElement).find('div.progress').addClass('hide');
                return file;
            });

            theAwesomeDropZone.on("cancelled",function(){
               if(typeof uploadmsg !== null)
               {
                   uploadmsg.hide();
               }
                Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
                    showCloseButton: true,
                    type: 'info',
                    hideAfter: 3,
                    message: 'Upload Cancelled: "'+file.name+'"'
                });
            });

            theAwesomeDropZone.on('error',function(file,errorMsg,xhr){
                Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
                    showCloseButton: true,
                    type: 'error',
                    hideAfter: 0,
                    message: 'Upload Failed: "'+file.name+'" - '+errorMsg
                });            
            });

            theAwesomeDropZone.on('uploadprogress',function(file,progress){
                var $uploadprogress = $('#upload-progress');
                if($uploadprogress[0].length != 0)
                {
                    $uploadprogress.animate({width:progress+'%'},500);
                }
            });

            theAwesomeDropZone.on('totaluploadprogress',function(progress){
               if(typeof uploadmsg !== "undefined" && progress == 100)
               {
                   uploadmsg.hide();
               }            
            });
            
            //File View
            $('#ot-docs').on('click','a.file-view',function(e){
               e.preventDefault();
               vex.open({
                   className: 'vex-theme-default vex-file-viewer',
                   content:'<div class="row"><div class="col-xs-12 text-align-center">'+$(this).html()+'</div></div><iframe src="http://docs.google.com/viewer?url='+encodeURIComponent($(this).attr('href'))+'&embedded=true" class="file-viewer"></iframe>',
               }).height($(window).height()).width($(window).width()*0.9);
               
               return false;
            });
            
            //Hide Loading Message
            messenger_hidenotiftop();
        }
        
    },
    
    ot_forms: function () {
        // DO NOT REMOVE : GLOBAL FUNCTIONS!
        pageSetUp();

        // PAGE RELATED SCRIPTS
        
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

	$("#inbox-table .inbox-data-message,#inbox-table .inbox-data-from").on('click',function() {
            $.pjax({
               url: $(this).closest('.orderform').attr('data-view'),
               container: '#main'
            });
	});
        
        $("#inbox-table a.markstar").on('click',function(){
            console.log('hey');
            var $this = $(this);
            
            if(typeof $this.data('ajax') !== "undefined")
            {
                $this.data('ajax').abort();
                $this.removeData('ajax');
            }
            
            if($this.children('i').hasClass('fa-star-o'))
            {
                $this.children('i').removeClass('fa-star-o');
                $this.children('i').addClass('fa-star');
            }
            else
            {
                $this.children('i').addClass('fa-star-o');
                $this.children('i').removeClass('fa-star');
            }
            return false;
//            var msg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
//                id: 'notif-top',
//                progressMessage: 'Working..',
//                action: $.ajax,
//            },
//            {
//                type:'PUT',
//                url: $this.attr('href'),
//                data: {'toggle': $this.hasClass('fa-star')},
//                beforeSend: function(){
//                    $this.data('ajax',$(this));
//                },
//                success:function()
//                {
//                    msg.hide();
//                    $this.removeData('ajax');
//                },
//                error:function(xhr, status, error)
//                {
//                    return xhr.responseText;
//                }
//            });            
        });
        
        //Hide Loading Message
        messenger_hidenotiftop();
    },
    
    /*
     * Freight Company - Forms
     */
    
    fc_forms: function () {
        // DO NOT REMOVE : GLOBAL FUNCTIONS!
        pageSetUp();

        // PAGE RELATED SCRIPTS
        
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

	$("#inbox-table .inbox-data-message,#inbox-table .inbox-data-from").on('click',function() {
            $.pjax({
               url: $(this).closest('.orderform').attr('data-view'),
               container: '#main'
            });
	});
        
        $("#inbox-table a.markstar").on('click',function(){
            console.log('hey');
            var $this = $(this);
            
            if(typeof $this.data('ajax') !== "undefined")
            {
                $this.data('ajax').abort();
                $this.removeData('ajax');
            }
            
            if($this.children('i').hasClass('fa-star-o'))
            {
                $this.children('i').removeClass('fa-star-o');
                $this.children('i').addClass('fa-star');
            }
            else
            {
                $this.children('i').addClass('fa-star-o');
                $this.children('i').removeClass('fa-star');
            }
            return false;
//            var msg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
//                id: 'notif-top',
//                progressMessage: 'Working..',
//                action: $.ajax,
//            },
//            {
//                type:'PUT',
//                url: $this.attr('href'),
//                data: {'toggle': $this.hasClass('fa-star')},
//                beforeSend: function(){
//                    $this.data('ajax',$(this));
//                },
//                success:function()
//                {
//                    msg.hide();
//                    $this.removeData('ajax');
//                },
//                error:function(xhr, status, error)
//                {
//                    return xhr.responseText;
//                }
//            });            
        });
        
        //Hide Loading Message
        messenger_hidenotiftop();
    },    
    
    /*
     * Nespresso CRM - Machine Create
    */
    ncrm_machinecreate: function() {
        // DO NOT REMOVE : GLOBAL FUNCTIONS!
	pageSetUp();
        
        $('#selectmachine').select2({
            placeholder: 'Enter a machine name',
            allowClear: true,
            minimumInputLength: 0,
            positionDropdownAbsolute: false,
            ajax: {
                 url: "/ajaxsearch/nespressomachine",
                 dataType: "json",
                 quietMillis: 500,
                 data: function (term, page) {
                     return {
                         term: term,
                         limit: 10,
                         page: page
                     };
                 },
                 results: function (data, page){
                     var more = (page * 15) < data.total
                     if(data.total > 0)
                     {
                         var found;
                         found = $.map(data.machines, function (item) {
                             item.LITM = $.trim(item.LITM);
                             return {
                                 id: item.LITM,
                                 name: item.DSC1,
                                 text: item.DSC1+" (Code:"+item.LITM+")",
                                 category: item.SRP3
                             }
                          });
                         return {results: found, more:more};
                     }
                     else
                     {
                         return {results: ''};
                     }
                 },
            },            
        });
        
        $('#selectcustomer').select2({
            placeholder: 'Enter a customer code/name',
            allowClear: true,
            minimumInputLength: 2,
            positionDropdownAbsolute: false,
            ajax: {
                 url: "/ajaxsearch/customercode",
                 dataType: "json",
                 quietMillis: 500,
                 data: function (term, page) {
                     return {
                         term: term,
                         limit: 10,
                         page: page
                     };
                 },
                 results: function (data, page){
                     var more = (page * 15) < data.total
                     if(data.total > 0)
                     {
                         var found;
                         found = $.map(data.customers, function (item) {
                             return {
                                 id: item.AN8,
                                 name: item.ALPH,
                                 text: item.ALPH+" (Code:"+item.AN8+")",
                                 category: item.AC09
                             }
                          });
                         return {results: found, more:more};
                     }
                     else
                     {
                         return {results: ''};
                     }
                 },
            },            
        });
        
        //Hide Loading Message
        messenger_hidenotiftop();
    }
}