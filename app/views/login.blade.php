<!DOCTYPE html>
<html lang="en-uk">
	<head>
		<meta charset="utf-8"/>
		<!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->

		<title> {{ Config::get('website.name') }} - Login </title>
		<meta name="description" content=""/>
		<meta name="author" content="Pudaruth Keshav"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>

		<!-- Basic Styles -->
        <link rel="stylesheet" href="{{Bust::url('/css/bootstrap.min.css')}}" />
        <link rel="stylesheet" href="{{Bust::url('/css/font-awesome.min.css')}}" />

		<!-- SmartAdmin Styles -->
        <link rel="stylesheet" href="{{Bust::url('/css/smartadmin-production.css')}}" />

		<!-- FAVICONS -->
		<link rel="shortcut icon" href="/img/favicon/favicon.ico" type="image/x-icon">
		<link rel="icon" href="/img/favicon/favicon.ico" type="image/x-icon">

		<!-- GOOGLE FONT -->
		<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700"/>

	</head>
	<body id="login" class="animated fadeInDown">
		<!-- possible classes: minified, no-right-panel, fixed-ribbon, fixed-header, fixed-width-->
		<header id="header">
			<div id="logo-group">
				<span id="logo">
                    <img src="/img/logo.png" alt="Scott Swift"/>
                </span>
			</div>
		</header>

		<div id="main" role="main">

			<!-- MAIN CONTENT -->
			<div id="content" class="container">
				@if(isset($msgalert))
					<div class="row">
						<div class="col-xs-12">
							@if($msgalert['status']==1)
								<div class="alert alert-danger fade in">
										<i class="fa-fw fa fa-times"></i>
										<strong>Error!</strong> {{ $msgalert['msg'] }}
								</div>
							@elseif($msgalert['status']==2)
								<div class="alert alert-warning fade in">
										<i class="fa-fw fa fa-warning"></i>
										<strong>Warning</strong> {{ $msgalert['msg'] }}
								</div>
							@endif
						</div>
					</div>
				@endif
				@if(Session::has('expired'))
					<div class="row">
						<div class="col-xs-12">
								<div class="alert alert-info fade in">
										<i class="fa-fw fa fa-info"></i>
										<strong>Info!</strong> Your session has expired. Please login to continue.
								</div>
						</div>
					</div>
				@endif
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 hidden-xs hidden-sm">
						<h1 class="txt-color-red login-header-big">Scott Swift</h1>
						<div class="hero">

							<div class="pull-left login-desc-box-l">
								<h4 class="paragraph-header">It's Okay to be Swift. Experience the simplicity of Scott Swift, everywhere you go!</h4>
							</div>
							<img src="/img/demo/iphoneview.png" class="pull-right display-image" alt="" style="width:210px">

						</div>

						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
								<h5 class="about-heading">About Scott Swift</h5>
								<p>
									All your information in one place. Collaborate in real-time with your colleagues.
								</p>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
								<h5 class="about-heading">Not just another information system!</h5>
								<p>
									Scott Swift brings forth progress tracking, to-do lists, document management and so much more.
								</p>
							</div>
						</div>

					</div>
					<div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
						<div class="well no-padding">
                            <div class="smart-form client-form">
                                <header>
                                    Sign In Using
                                </header>
                                <fieldset>
                                    <ul class="list-inline text-center">
                                        <li>
                                            <a href="{{ $googleAuthUrl }}" class="btn btn-primary btn-circle btn-lg" title="Google"><i class="fa fa-google-plus"></i></a>
                                        </li>
                                    </ul>
                                </fieldset>
                                <footer>
                                    By signing in, you agree to <a href="{{action('TOSController@terms')}}" target="_blank">our terms & conditions.</a>
                                </footer>
                            </div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>