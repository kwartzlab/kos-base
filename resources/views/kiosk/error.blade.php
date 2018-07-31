@extends('kiosk')

@section ('customhead')
<meta http-equiv="refresh" content="3; url=/kiosk">
@endsection

@section('content')

<div style="text-align:center;">

<img src="/img/kiosk/error.png" style="height:200px"><br />
	      <h1><strong>{{ $error_message }}</strong></h1>


</div>

@endsection