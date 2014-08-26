<!-- Left panel : Navigation area -->
		<!-- Note: This width of the aside area can be adjusted through LESS variables -->
		<aside id="left-panel">

			<!-- User info -->
			<div class="login-info">
				<span> <!-- User image size is adjusted inside CSS, it should stay as it --> 
					
					<a href="javascript:void(0);" id="show-shortcut">
                                                {{\Swift\Avatar::getHTML(false,true)}}
						<span class="text">
							{{ Sentry::getUser()['first_name'] }} {{ Sentry::getUser()['last_name'] }}
						</span>
                                                <!-- Disabled Top Menu -->
<!--						<i class="fa fa-angle-down"></i>-->
					</a> 
					
				</span>
			</div>
			<!-- end user info -->

			<!-- NAVIGATION : This navigation is also responsive

			To make this navigation dynamic please make sure to link the node
			(the reference to the nav > ul) after page load. Or the navigation
			will not initialize.
			-->
			<nav id="leftsidemenu">
                                {{ $sidemenu }}
			</nav>
			<span class="minifyme"> <i class="fa fa-arrow-circle-left hit"></i> </span>

		</aside>
		<!-- END NAVIGATION -->