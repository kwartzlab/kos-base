@extends('kiosk')

@section ('customhead')
@endsection

@section('content')

<h2 style="margin-bottom:20px"><strong>Hello {{ Auth::user()->get_name('first') }}</strong></h2>

<div class="row">
   <div class="col-xs-6 col-xs-offset-3">
      @if(\Gate::allows('manage-keys'))
         <a href="/kiosk/create_key" class="btn btn-warning btn-lg btn-block">Assign Key to User</a>
      @endif
         <a href="/kiosk/logout" class="btn btn-primary btn-lg btn-block">Logout</a>
   </div>
</div>

@endsection

