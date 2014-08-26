@extends('layout')

@section('content')
<!-- RIBBON -->
<div id="ribbon">

        <span class="ribbon-button-alignment"> <span id="refresh" class="btn btn-ribbon" data-title="refresh"  rel="tooltip" data-placement="bottom" data-original-title="<i class='text-warning fa fa-warning'></i> Warning! This will reset all your widget settings." data-html="true"><i class="fa fa-refresh"></i></span> </span>

        <!-- breadcrumb -->
        <ol class="breadcrumb">
                <li>
                        Error 403
                </li>
        </ol>
        <!-- end breadcrumb -->

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content">

        <!-- row -->
        <div class="row">

                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

                        <div class="row">
                                <div class="col-sm-12">
                                        <div class="text-center error-box">
                                                <h1 class="error-text tada animated"><i class="fa fa-minus-circle text-danger error-icon-shadow"></i> Error 403</h1>
                                                <h2 class="font-xl"><strong>Oooops, You don't have access!</strong></h2>
                                                <br />
                                                <p class="lead semi-bold">
                                                    <strong>That's all we know. <a href="javascript:history.back();">Click here to go back</a></strong><br><br>
                                                        <small>
                                                            If you feel this is an error, <a href="#">contact your webmaster by clicking here.</a>
                                                        </small>
                                                </p>
                                        </div>

                                </div>

                        </div>

                </div>

        </div>
        <!-- end row -->

</div>
<!-- END MAIN CONTENT -->

@stop