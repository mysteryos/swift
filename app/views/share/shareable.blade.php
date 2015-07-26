<!DOCTYPE html>
<html lang="en-us">
    <head>
        <title>Share</title>
        
		<!-- GOOGLE FONT -->
		<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700">

        <!-- Main CSS -->
        <link rel="stylesheet" type="text/css" href="{{\Bust::url('/css/all.css') }}"/>
    </head>
    <body>
        <div class="container" id="share_container">
            <div class="row">
                <div class="col-xs-12 well well-sm">
                    <span class="text-capitalize text-center h4 col-xs-12">Share</span>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    @if(count($form->share))
                        <table class="table table-borderless">
                            <tr>
                                <th>Date Shared</th>
                                <th>By</th>
                                <th>To</th>
                                <th>Permission</th>
                                <th></th>
                            </tr>
                            @foreach($form->share as $s)
                            <tr class="share-row">
                                <td><span title="{{$s->created_at->toDayDateTimeString()}}">{{$s->created_at->toFormattedDateString()}}</span></td>
                                <td>@if($s->from_user){{$s->from_user->getFullName()}}@endif</td>
                                <td>@if($s->to_user){{$s->to_user->getFullName()}}@endif</td>
                                <td>{{$s->permission_name}}</td>
                                <td><a class="btn btn-default btn-delete-share" href="/share/delete/{{\Crypt::encrypt($s->id)}}"><i class="fa fa-times"></i></a></td>
                            </tr>
                            @endforeach
                        </table>
                    @else
                        <p class="text-center">No shares found.</p>
                    @endif
                </div>
            </div>
            <div class="well well-sm">
                <div class="row">
                    <div class="col-xs-12">
                        <form action="/share/save" type="POST" id="share_form" class="form">
                            <div class="row">
                                <div class="col-xs-12">
                                    <span>Invite People:</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6 form-group">
                                    <select name="user_id" autocomplete="off" class="form-control" id="select_share_user_id">
                                        <option selected disabled>Select a User</option>
                                        @foreach($users as $u)
                                            <option value="{{$u->id}}">{{$u->getFullName()}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xs-3 form-group">
                                    <select name="permission" autocomplete="off" class="form-control">
                                        @foreach($permission as $p_k => $p_v)
                                            <option value="{{$p_k}}" @if($p_k === \SwiftShare::PERMISSION_VIEW){{"selected"}}@endif>{{$p_v}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="notify_mail" checked="checked" id="notify_mail_check"/> Notify people via mail.
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row msg-row form-group">
                                <div class="col-xs-12">
                                    <textarea placeholder="Option: Include a personal message" autocomplete="off" name="msg" class="form-control" cols="6"></textarea>
                                </div>
                            </div>
                            <div class="row row-space-top-2">
                                <div class="col-xs-12">
                                    <a class="btn btn-primary col-xs-2" href="/share/save/{{\Helper::resolveContext(get_class($form))}}/{{\Crypt::encrypt($form->id)}}" id="btn-send">Send</a>
                                    <button class="btn btn-default col-xs-2 col-xs-offset-1" id="btn-cancel">Cancel</button>
                                </div>
                            </div>
                        </form>
                </div>
            </div>
        </div>
        <script type="text/javascript" src="{{\Bust::url('/js/swift/swift.share.js')}}"></script>
    </body>
</html>