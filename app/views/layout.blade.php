@include('head')

@yield('content')
<input id="site_version" type="hidden" value="{{\Config::get('website.version')}}" />
@include('footer')