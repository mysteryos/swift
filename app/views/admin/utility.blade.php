@extends ('layout')

@section ('content')

<!-- RIBBON -->
<div id="ribbon">
        <div class="ribbon-button-alignment">
            <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
        </div>
</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="admin_utility" data-urljs="{{Bust::url('/js/swift/swift.admin_utility.js')}}">
    <div class="row">
        <div class="col-xs-12 col-sm-7 col-md-7 col-lg-4">
                <h1 class="page-title txt-color-blueDark"><i class="fa-fw fa fa-check"></i> Admin <span>> Utility</span></h1>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <fieldset>
                <legend>MSSQL Sync Code Generator</legend>
                <form name="mssql_sync" method="POST" id="mssql_sync_form" action="/admin/mssql-sync">
                    <div class="row">
                        <div class="form-group">
                            <div class="col-xs-4">
                                <label class="control-label">Paste your SQL Select Statement Here:</label>
                            </div>
                            <div class="col-xs-8">
                                <textarea rows="4" name="sqlstatement" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <div class="col-xs-4">
                                <label class="control-label">SQL Master Table Name</label>
                            </div>
                            <div class="col-xs-8">
                                <input type="text" name="master_table_name" class="form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <div class="col-xs-4">
                                <label class="control-label">Timestamps</label>
                            </div>
                            <div class="col-xs-8">
                                <input type="checkbox" name="timestamps" class="form-control" value="1" checked="checked" />
                            </div>
                        </div>
                    </div>
                    <div class="row row-result well" style="display:none;">
                        <div class="col-xs-4">
                            <label class="form-control-static">Result:</label>
                        </div>
                        <div class="col-xs-8">
                            <textarea class="col-result"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <input type="submit" class="pull-right btn btn-lg btn-default btn-submit" value="Submit" />
                        </div>
                    </div>
                </form>
            </fieldset>
        </div>
    </div>
</div>

@stop