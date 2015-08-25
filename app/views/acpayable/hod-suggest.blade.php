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
        <div id="acp_hod_suggest_container" class="colorbox-container">
                <div class="row">
                    <div class="col-xs-12">
                        <form action="/{{$rootURL}}/hod-suggestion/{{\Crypt::encrypt($form->id)}}" type="POST" id="acp_hod_suggestion_form" class="form">
                            <div class="row row-space-2">
                                <div class="col-xs-12">
                                    <h4>Suggest Colleagues for Approval:</h4>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 form-group">
                                    <input type="hidden" id="acp_hod_suggest_select" name="acp_hod_suggest_select" class="full-width" value="" />
                                    <input type="hidden" id="acp_hod_suggest_user_list" value='{{json_encode($users)}}'/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="remove_approval" checked="checked" id="remove_approval"/> Remove my approval from this form.
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="notify_mail" checked="checked" id="notify_mail_check"/> Notify of requested approval via mail.
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row row-space-top-2">
                                <div class="col-xs-12">
                                    <a class="btn btn-primary col-xs-5" href="/{{$rootURL}}/hod-suggestion/{{\Crypt::encrypt($form->id)}}" id="btn-send">Send</a>
                                    <button class="btn btn-default col-xs-5 col-xs-offset-2" id="btn-cancel">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
        <script type="text/javascript" src="{{\Bust::url('/js/swift/swift.acp_hod_suggest.js')}}"></script>
    </body>
</html>