<!DOCTYPE html>
<html lang="en-us">
	<head>
		<meta charset="utf-8"/>
		<meta http-equiv="x-ua-compatible" content="IE=Edge">

		<title>{{ $pageTitle }} - {{ Config::get('website.name') }}</title>
		<meta name="description" content="Scott Swift">
		<meta name="author" content="Pudaruth Keshav">
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<!-- FAVICONS -->
		<link rel="shortcut icon" href="/img/favicon/favicon.ico" type="image/x-icon">
		<link rel="icon" href="/img/favicon/favicon.ico" type="image/x-icon">

		<!-- GOOGLE FONT -->
		<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700">
                
                <!-- Main CSS -->
                <link rel="stylesheet" type="text/css" href="/css/all.css"/>
                
	</head>
        <body class="fixed-header fixed-ribbon smart-style-3" style="overflow:hidden;position:relative;">
            <!-- PRE-LOADER -->
            @include('preloader')
            <!-- END PRE-LOADER -->
            
            <!-- HEADER -->
            @include('header')
            <!-- END HEADER -->
            
            <!-- Left panel : Navigation area -->
            @include('navigation')
            <!-- END NAVIGATION -->

            <!-- MAIN PANEL -->
            <div id="main" role="main">