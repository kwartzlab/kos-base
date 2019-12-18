<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>@yield('title', 'kwartzlabOS') [kOS]</title>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  
   <link href="{{ asset("/vendor/bootstrap/dist/css/bootstrap.min.css") }}" rel="stylesheet" type="text/css" />
   <link href="{{ asset("/vendor/fontawesome-free/css/all.min.css") }}" rel="stylesheet" type="text/css" />
   <link href="{{ asset("/css/kiosk.css")}}" rel="stylesheet" type="text/css" />

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
   @yield('customhead')

</head>

<body class="hold-transition">

   <div class="wrapper">

   @if (Auth::check())

   @endif

   <!-- Content Wrapper. Contains page content -->
   <div class="content-wrapper"  style="margin:0">

      <div class="row">
      <div class="col-lg-1 center-block {{ $vcenter ?? "vcenter" }}" style="text-align:center;width:95%;>
         
         @yield('content')

      </div>
      </div>

   </div>
   <!-- /.content-wrapper -->
   
   </div>
   <!-- ./wrapper -->

   <!-- REQUIRED JS SCRIPTS -->

   <script src="{{ asset("/vendor/jquery/jquery.min.js") }}"></script>
   <script src="{{ asset("/vendor/bootstrap/js/bootstrap.bundle.min.js") }}"></script>

   @yield('customjs')

</body>
</html>
