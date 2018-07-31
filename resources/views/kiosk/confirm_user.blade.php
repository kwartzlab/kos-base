@extends('kiosk')

@section ('customhead')
<meta http-equiv="refresh" content="30; url=/kiosk">
@endsection

@section('content')

<div style="text-align:center;">

	<h2 style="margin-bottom:30px">Please Confirm User Assignment</h2>

	<h1><strong>{{ $keyuser->name }}</strong></h1>

	<div style="width:75%;margin:0 auto;">

	<form method="POST" action="/kiosk/store_key">
	{{ csrf_field() }}
	<input type="hidden" name="rfid" value="{{ $rfid }}">
	<input type="hidden" name="user_id" value="{{ $keyuser->id }}">

		<p><a href="/kiosk" class="btn btn-danger btn-lg confirmbutton" style="float:left;">No</a></button><button type="submit" class="btn btn-success btn-lg confirmbutton" style="float:right;">Yes</button></p>

	</form>
	</div>
</div>

@endsection

