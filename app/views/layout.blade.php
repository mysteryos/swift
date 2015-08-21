@include('head')

@yield('content')
<input id="site_version" type="hidden" value="{{\Config::get('website.version')}}" />
<input id="pusher_app_id" type="hidden" value="{{\Config::get('pusher.app_key')}}" />
@include('footer')