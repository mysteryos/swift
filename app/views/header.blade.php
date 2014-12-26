<!-- HEADER -->
<header id="header">
        <div id="logo-group">

                <!-- PLACE YOUR LOGO HERE -->
                <span id="logo"> <img src="/img/logo.png" alt="Scott Swift"> </span>
                <!-- END LOGO PLACEHOLDER -->

                <!-- Notifications -->
                
                @include('notification.notification-list');
        </div>

        <!-- projects dropdown -->
        <div id="project-context">

                <span class="label">Projects:</span>
                <span id="project-selector" class="popover-trigger-element dropdown-toggle" data-toggle="dropdown">Recent projects <i class="fa fa-angle-down"></i></span>

                <!-- Suggestion: populate this list with fetch and push technique -->
                <ul class="dropdown-menu">
                        <li class="divider"></li>
                        <li>
                                <a id="clear-project" href="javascript:void(0);"><i class="fa fa-power-off"></i> Clear</a>
                        </li>
                </ul>
                <!-- end dropdown-menu-->

        </div>
        <!-- end projects dropdown -->
        <div>
            <!-- input: search field -->
            <form action="/search" class="header-search" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search anything..." id="search-fld">
                    <div class="input-group-btn">
                        <button type="submit" class="btn btn-default">Search</button>
                    </div>
                </div>
            </form>
        </div>
        <!-- end input: search field -->
        <!-- pulled right: nav area -->
        <div class="pull-right">

                <!-- collapse menu button -->
                <div id="hide-menu" class="btn-header pull-right">
                        <span> <a href="javascript:void(0);" title="Collapse Menu"><i class="fa fa-reorder"></i></a> </span>
                </div>
                <!-- end collapse menu -->

                <!-- logout button -->
                <div id="logout" class="btn-header transparent pull-right">
                        <span> <a href="/login/logout" title="Sign Out"><i class="fa fa-sign-out"></i></a> </span>
                </div>
                <!-- end logout button -->

                <!-- search mobile button (this is hidden till mobile view port) -->
                <div id="search-mobile" class="btn-header transparent pull-right">
                        <span> <a href="javascript:void(0)" title="Search"><i class="fa fa-search"></i></a> </span>
                </div>
                <!-- end search mobile button -->

        </div>
        <!-- end pulled right: nav area -->

</header>
<!-- END HEADER -->