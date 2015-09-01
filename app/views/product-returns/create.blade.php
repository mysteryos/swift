@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

    <div class="ribbon-button-alignment hidden-xs">
        <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
    </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="pr_create" data-urljs="{{Bust::url('/js/swift/swift.pr_create.js')}}">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <!-- PAGE HEADER -->
                <i class="fa-fw fa fa-reply"></i>
                    Product Returns: {{$type_name}}
                <span>&gt;
                    Create
                </span>
            </h1>
        </div>
    </div>

    <!-- widget grid -->
    <section id="widget-grid">

	<!-- START ROW -->

	<div class="row">
        <form id="pr_create_form" action="/{{$rootURL}}/create/{{$type}}" method="post" class="form-horizontal" name="pr_create_form">
            <!-- NEW COL START -->
            <article class="col-xs-12">

                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="widget-form" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-togglebutton="false">

                    <header>
                        <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                        <h2>Create Form </h2>

                    </header>
                    <div>
                        <div class="widget-body">
                                <fieldset>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4 col-md-2">Customer</label>
                                        <div class="controls col-xs-8 col-md-10">
                                            <input type="hidden" id="ccode" name="customer_code" class="full-width" autocomplete="off" value="" />
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                </fieldset>
                        </div>
                    </div>
                </div>
            </article>
            <div class="row">
                <div class="col-xs-12">
                    <button id="btn-reset" class="btn btn-default col-xs-offset-2 col-xs-3" type="reset">Reset</button>
                    <button id="btn-publish" class="btn btn-danger col-xs-offset-1 col-xs-3" type="submit">Save Draft</button>
                </div>
            </div>
        </form>
	</div>

	<!-- END ROW -->

    </section>
    <!-- end widget grid -->
</div>

@stop