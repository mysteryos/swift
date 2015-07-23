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
        <link rel="stylesheet" type="text/css" href="{{ Bust::url('/css/all.css') }}"/>
        <link rel="stylesheet" type="text/css" href="{{ Bust::url('/css/document-viewer.css') }}" />
	</head>
    <body>
        <header>
            <a class="title h4" href="{{\Helper::generateURL($form)}}" target="_blank"><i class="fa {{$form->getIcon()}}"></i> {{$form->getReadableName()}}</a>
        </header>
        <div id="doc-container">
            @if(count($form->document) > 0)
                <?php
                $first_doc = $form->document->first();
                switch($first_doc->getAttachedfiles()['document']->contentType())
                {
                    case "image/jpeg":
                    case "image/png":
                    case "image/bmp":
                    case "image/jpg":
                        echo '<img class="image-view" src="'.$first_doc->getAttachedfiles()["document"]->url().'"/>';
                        break;
                    case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                    case "application/vnd.ms-excel":
                    case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                    case "application/msword":
                        echo '<iframe class="document-iframe" src="https://docs.google.com/viewerng/viewer?url='.$first_doc->getAttachedfiles()["document"]->url().'"></iframe>';
                        break;
                    case "application/pdf":
                        echo '<iframe class="document-iframe" src="/pdfviewer/viewer.html?file='.$first_doc->getAttachedfiles()["document"]->url().'"></iframe>';
                        break;
                    default:
                        echo '<iframe class="document-iframe" src="https://docs.google.com/viewerng/viewer?url='.$first_doc->getAttachedfiles()["document"]->url().'"></iframe>';
                        break;
                }
                ?>
            @else
                <iframe></iframe>
                <div id="no-doc">
                    <div class="text-center"><i class="fa fa-file-pdf-o"></i></div>
                    <h2 class="text-center">No Documents Found.</h2>
                </div>
            @endif
        </div>
        <div id="doc-browser">
            @if(count($form->document) > 0)
                <ul class="doc-list" id="doc-list-{{$form->id}}">
                @foreach($form->document as $k => $doc)
                    <li data-href="{{$doc->external_url}}" @if($k===0)class="doc-selected"@endif>
                        <div class="doc-icon">
                            <?php
                            switch($doc->getAttachedfiles()['document']->contentType())
                            {
                                case "image/jpeg":
                                case "image/png":
                                case "image/bmp":
                                case "image/jpg":
                                    echo '<i class="fa fa-file-image-o"></i>';
                                    break;
                                case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                                case "application/vnd.ms-excel":
                                    echo '<i class="fa fa-file-excel-o"></i>';
                                    break;
                                case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                                case "application/msword":
                                    echo '<i class="fa fa-file-word-o"></i>';
                                    break;
                                case "application/pdf":
                                    echo '<i class="fa fa-file-pdf-o"></i>';
                                    break;
                                default:
                                    echo '<i class="fa fa-file-o"></i>';
                                    break;
                            }
                            ?>
                        </div>
                        <div class="doc-name">
                            {{$doc->getAttachedfiles()['document']->originalFilename()}}
                        </div>
                    </li>
                @endforeach
                </ul>
            @endif
        </div>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script type="text/javascript" src="{{\Bust::url('/js/swift/swift.document-viewer.js')}}"></script>
    </body>
</html>