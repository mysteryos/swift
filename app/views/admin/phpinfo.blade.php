@extends ('layout')

@section ('content')

<!-- RIBBON -->
<div id="ribbon">
        <div class="ribbon-button-alignment">
            <ol class="breadcrumb"><li>Home</li><li>Administration</li><li>PHP Info</li></ol>            
        </div>        
</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content">
    <div class="row">
        <div class="col-xs-12">
            {{ phpinfo() }}
        </div>
    </div>
</div>

@stop