                <?php 
                if (isset($before_js))
                {
                    foreach($before_js as $js)
                    {
                        echo "<script type=\"text/javascript\" src=\"{$js}\"></script>";
                    }
                }
                ?>
                </div>
		<!-- END MAIN PANEL -->
                
                <!-- STICKY FOOTER START -->
                <footer class="sticky-footer">
                </footer>
                
                <!-- STICKY FOOTER END -->
                
                <!-- SHORTCUT AREA : With large tiles (activated via clicking user name tag)
		Note: These tiles are completely responsive,
		you can add as many as you like
		-->
		<div id="shortcut">
			<ul>
				<li>
					<a href="#inbox.html" class="jarvismetro-tile big-cubes bg-color-blue"> <span class="iconbox"> <i class="fa fa-envelope fa-4x"></i> <span>Mail <span class="label pull-right bg-color-darken">14</span></span> </span> </a>
				</li>
				<li>
					<a href="#calendar.html" class="jarvismetro-tile big-cubes bg-color-orangeDark"> <span class="iconbox"> <i class="fa fa-calendar fa-4x"></i> <span>Calendar</span> </span> </a>
				</li>
				<li>
					<a href="#gmap-xml.html" class="jarvismetro-tile big-cubes bg-color-purple"> <span class="iconbox"> <i class="fa fa-map-marker fa-4x"></i> <span>Maps</span> </span> </a>
				</li>
				<li>
					<a href="#invoice.html" class="jarvismetro-tile big-cubes bg-color-blueDark"> <span class="iconbox"> <i class="fa fa-book fa-4x"></i> <span>Invoice <span class="label pull-right bg-color-darken">99</span></span> </span> </a>
				</li>
				<li>
					<a href="#gallery.html" class="jarvismetro-tile big-cubes bg-color-greenLight"> <span class="iconbox"> <i class="fa fa-picture-o fa-4x"></i> <span>Gallery </span> </span> </a>
				</li>
				<li>
					<a href="javascript:void(0);" class="jarvismetro-tile big-cubes selected bg-color-pinkDark"> <span class="iconbox"> <i class="fa fa-user fa-4x"></i> <span>My Profile </span> </span> </a>
				</li>
			</ul>
		</div>
		<!-- END SHORTCUT AREA -->

		<!--================================================== -->
                
                <script type="text/javascript" src="/js/plugin/preloadjs/preloadjs-0.4.1.min.js"></script>
                <script type="text/javascript">
                    //Pace options
                    window.paceOptions = {
                        ghostTime: 500,
                        restartOnPushState: true
                    }
                    //Preloader Script
                    var queue = new createjs.LoadQueue(false);
                    var loaderrorcount = 0;
                    queue.setMaxConnections(3);
                    var progress = 0;
                    queue.on('progress',function(e){
                        if(progress < e.progress*100)
                        {
                            progress = e.progress*100;
                            document.getElementById('pl-loading-bar').style.width = progress.toString()+"%";
                        }
                    },this);
                    queue.on('complete',function(e){
                        //Run Main Script for Page
                        if(typeof $('#content').attr('data-js') !== "undefined")
                        {
                            main[$('#content').attr('data-js').toString()]();
                        }
                        window.setTimeout(function(){
                            var loadingdiv = document.getElementById('loading');
                            loadingdiv.parentNode.removeChild(loadingdiv);                            
                            //document.getElementsByTagName("body")[0].removeAttribute('style');
                            clearTimeout(slowconnection);
                        },500);
                    },this);
                    queue.on('error',function(e){
                        clearTimeout(slowconnection);
                        if(loaderrorcount == 0)
                        {
                            document.getElementById('loadingError').style.display = "block";
                        }
                        if(loaderrorcount < 5)
                        {
                            queue.reset();
                            queue.load();
                        }
                        else
                        {
                            document.getElementById('loadingError'.style.innerHTML("Loading of page has failed. Please refresh and try again."));
                        }

                        loaderrorcount++;
                    });
                    var slowconnection = window.setTimeout(function(){
                            document.getElementById('slowConnectionError').style.display = "block";
                    },30000);
                    var assets = <?php echo isset($assets) ? '['.$assets.'];' : ''; ?>
                    <?php 
                    if(isset($assets))
                    { ?>
                        queue.loadManifest(
                            assets
                        );
                    <?php } ?>
                </script>

	</body>

</html>