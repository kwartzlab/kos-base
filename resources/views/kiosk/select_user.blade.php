@extends('kiosk')

@section ('customhead')
<meta http-equiv="refresh" content="60; url=/kiosk">
@endsection

@section('content')

<div style="text-align:center;">

	<h3 style="margin-bottom:30px"><strong>Select User to Assign Key (scroll for more)</strong></h3>

	<div style="position: absolute;overflow-y: scroll;height:400px;width:100%">
		<div style="width:75%;margin:0 auto;">

		<form method="POST" action="/kiosk/create_key">
		{{ csrf_field() }}
		<input type="hidden" name="rfid" value="{{ $rfid }}">

		@foreach ($users as $user)
			<button type="submit" name="user_id" class="btn btn-primary btn-lg btn-block userbutton" value="{{ $user->id }}">{{ $user->name }}</button>

		@endforeach


		</form>
		</div>
	</div>
</div>

@endsection

