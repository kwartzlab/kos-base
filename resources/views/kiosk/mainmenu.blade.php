@extends('kiosk')

@section ('customhead')
<meta http-equiv="refresh" content="120; url=/kiosk/logout">
@endsection

@section('content')

<div style="text-align:center;">

	      <h2 style="margin-bottom:20px"><strong>Hello {{ Auth::user()->first_name }}</strong></h2>

	      <div style="width:50%;margin:0 auto">

	      <!-- <a href="/kiosk/unlock" class="btn btn-success btn-lg btn-block bigbutton">Unlock Front Door</a> -->

		  @if (Auth::user()->acl == 'admin')

		      <a href="/kiosk/create_key" class="btn btn-warning btn-lg btn-block bigbutton">Assign Key to User</a>
		      <!-- <a href="/kiosk/authorizations" class="btn btn-warning btn-lg btn-block bigbutton">Manage Authorizations</a> -->

	      @endif

	      <a href="/kiosk/logout" class="btn btn-primary btn-lg btn-block bigbutton">Logout</a>

           </div>

</div>

@endsection

