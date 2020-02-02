<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
      <meta http-equiv="Pragma" content="no-cache" />
      <meta http-equiv="Expires" content="0" />

      <title>@if(isset($page['title'])){{ $page['title'] }}@endif [kOS]</title>

      <link href="{{ asset("/vendor/bootstrap/dist/css/bootstrap.min.css") }}" rel="stylesheet" type="text/css" />
      <link href="{{ asset("/vendor/fontawesome-free/css/all.min.css") }}" rel="stylesheet" type="text/css" />
      <link href="{{ asset("/css/kiosk.css")}}" rel="stylesheet" type="text/css" />

      @yield('customhead')
      @if (isset($page['refresh']))
         <meta http-equiv="refresh" content="{{ $page['refresh'] }}; url={{ $page['refresh_url'] }}">
      @endif
   </head>

   <body @if(isset($page['use_navbar'])) style="margin-top:60px;" @endif>
      @yield('navbar')
      <div class="container-fluid vcenter">
         <div class="row" style="width:100%;">
            @yield('content')
         </div>
      </div>
      <script src="{{ asset("/vendor/jquery/jquery.min.js") }}"></script>
      <script src="{{ asset("/vendor/bootstrap/js/bootstrap.bundle.min.js") }}"></script>
      @yield('customjs')
   </body>
</html>
