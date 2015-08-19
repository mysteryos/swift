@extends ('layout')

@section ('content')

<!-- RIBBON -->
<div id="ribbon">
        <div class="ribbon-button-alignment">
            <a class="btn btn-default" href="javascript:void(0);"><i class="fa fa-plus"></i> Add User</a>
            <a class="btn btn-default" href="javascript:void(0);"><i class="fa fa-trash-o"></i> Deactivate</a>
        </div>
</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content"  data-js="admin_users" data-urljs="{{Bust::url('/js/swift/swift.admin_users.js')}}">
        <div class="row">
            <div class="col-xs-12 col-sm-7 col-md-7 col-lg-4">
                    <h1 class="page-title txt-color-blueDark"><i class="fa-fw fa fa-home"></i> Admin <span>> Users</span></h1>
            </div>
        </div>
    
        <div class="row">
            <div class="well well-light">
                <table class="table table-hover">
                    <thead>
                            <tr>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Created At</th>
                                    <th>Last Login</th>
                                    <th colspan="2">Actions</th>
                            </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr>
                                <td>{{ $u->first_name }}</td>
                                <td>{{ $u->last_name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->created_at }}</td>
                                <td>{{ $u->last_login }}</td>
                                <td><a href="/user/{{{ $u->email }}}"><i class="fa fa-edit"></i> Modify</a></td>
                                <td>@if($u->activated === 1)<a href="/admin/login-as/{{$u->email}}" class="ajax-login"><i class="fa fa-user"></i> Login</a>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
</div>

@stop